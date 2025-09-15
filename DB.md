# Database Schema Documentation

## 1. MySQL (Source of Truth)

### 0. Tenancy (Shared Schema, Row-Level Isolation)

#### Tenants
```sql
CREATE TABLE tenants (
  id BINARY(16) PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(64) NOT NULL UNIQUE,
  domain VARCHAR(255) NULL,
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL
) ENGINE=InnoDB;
```

> All business tables include `tenant_id BINARY(16)` and composite keys/constraints so data is isolated per tenant. Uniqueness constraints (e.g., product `slug`, variant `sku`) are **per tenant**.

### 1.1 Catalog Tables

#### Products
```sql
CREATE TABLE products (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  title VARCHAR(255) NOT NULL,
  brand VARCHAR(120) NULL,
  description TEXT NULL,
  attributes JSON NULL,               -- flexible attributes
  status ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  UNIQUE KEY uniq_products_slug_per_tenant (tenant_id, slug),
  KEY idx_products_brand_title (tenant_id, brand, title),
  CONSTRAINT fk_products_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

#### Product Variants
```sql
CREATE TABLE product_variants (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  product_id BINARY(16) NOT NULL,
  sku VARCHAR(64) NOT NULL,
  price_cents BIGINT NOT NULL,
  currency CHAR(3) NOT NULL,
  attr JSON NULL,                     -- per-variant attributes
  weight_g INT NULL,
  barcode VARCHAR(64) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  color VARCHAR(32)
    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(attr,'$.color'))) STORED,
  size  VARCHAR(32)
    GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(attr,'$.size'))) STORED,
  PRIMARY KEY (tenant_id, id),
  UNIQUE KEY uniq_variants_sku_per_tenant (tenant_id, sku),
  KEY idx_variants_product (tenant_id, product_id),
  KEY idx_variants_color (tenant_id, color),
  KEY idx_variants_size (tenant_id, size),
  CONSTRAINT fk_variants_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_variants_product FOREIGN KEY (tenant_id, product_id)
    REFERENCES products(tenant_id, id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

#### Categories
```sql
CREATE TABLE categories (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  parent_id BINARY(16) NULL,
  slug VARCHAR(160) NOT NULL,
  name VARCHAR(160) NOT NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  UNIQUE KEY uniq_categories_slug_per_tenant (tenant_id, slug),
  KEY idx_categories_parent (tenant_id, parent_id),
  CONSTRAINT fk_categories_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_categories_parent FOREIGN KEY (tenant_id, parent_id)
    REFERENCES categories(tenant_id, id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE product_category (
  tenant_id BINARY(16) NOT NULL,
  product_id BINARY(16) NOT NULL,
  category_id BINARY(16) NOT NULL,
  PRIMARY KEY (tenant_id, product_id, category_id),
  CONSTRAINT fk_pc_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pc_product FOREIGN KEY (tenant_id, product_id)
    REFERENCES products(tenant_id, id) ON DELETE CASCADE,
  CONSTRAINT fk_pc_category FOREIGN KEY (tenant_id, category_id)
    REFERENCES categories(tenant_id, id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

#### Product Media
```sql
CREATE TABLE product_media (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  product_id BINARY(16) NOT NULL,
  product_variant_id  BINARY(16) NULL,
  url VARCHAR(512) NOT NULL,
  alt VARCHAR(255) NULL,
  position INT NOT NULL DEFAULT 0,
  created_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  KEY idx_media_product (tenant_id, product_id),
  KEY idx_media_variant (tenant_id, product_variant_id),
  CONSTRAINT fk_media_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_media_product FOREIGN KEY (tenant_id, product_id) REFERENCES products(tenant_id, id) ON DELETE CASCADE,
  CONSTRAINT fk_media_variant FOREIGN KEY (tenant_id, product_variant_id) REFERENCES product_variants(tenant_id, id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

### 1.2 Customers & Addresses

#### Customers
```sql
CREATE TABLE customers (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  email VARCHAR(254) NOT NULL,
  name VARCHAR(255) NULL,
  phone VARCHAR(40) NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  UNIQUE KEY uniq_customers_email_per_tenant (tenant_id, email),
  CONSTRAINT fk_customers_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE addresses (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  customer_id BINARY(16) NOT NULL,
  type ENUM('billing','shipping') NOT NULL,
  payload JSON NOT NULL, -- {line1, line2, city, country, zip, ...}
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  KEY idx_addr_customer (tenant_id, customer_id),
  CONSTRAINT fk_addr_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_addr_customer FOREIGN KEY (tenant_id, customer_id)
    REFERENCES customers(tenant_id, id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### 1.3 Inventory Management

> **Pattern**: On checkout, `SELECT ... FOR UPDATE` the row in `inventory_levels`, compute `available = on_hand - reserved`, 
> bump `reserved`, insert a reservation with TTL; also insert a Redis key `stock_resv:{sku}:{order}` with same TTL for fast checks. 
> Reconciliation job releases expired reservations.

#### Inventory Levels
```sql
CREATE TABLE inventory_levels (
  tenant_id BINARY(16) NOT NULL,
  sku VARCHAR(64) NOT NULL,
  location_id VARCHAR(32) NOT NULL DEFAULT 'default',
  on_hand INT NOT NULL,
  reserved INT NOT NULL DEFAULT 0,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, sku, location_id),
  CONSTRAINT fk_inv_levels_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE inventory_reservations (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  sku VARCHAR(64) NOT NULL,
  order_id BINARY(16) NOT NULL,
  qty INT NOT NULL,
  expires_at DATETIME(6) NOT NULL,
  created_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  UNIQUE KEY uniq_resv (tenant_id, sku, order_id),
  KEY idx_resv_expires (tenant_id, expires_at),
  CONSTRAINT fk_inv_resv_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE inventory_movements (
  tenant_id BINARY(16) NOT NULL,
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  sku VARCHAR(64) NOT NULL,
  delta INT NOT NULL,            -- +restock / -shipment
  reason VARCHAR(64) NOT NULL,   -- adjustment, order_shipment, return, etc.
  ref_id BINARY(16) NULL,        -- order/return id
  created_at DATETIME(6) NOT NULL,
  KEY idx_inv_move_sku_created (tenant_id, sku, created_at),
  CONSTRAINT fk_inv_moves_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

### 1.4 Orders & Checkout

#### Cart Snapshots
```sql
CREATE TABLE cart_snapshots (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  customer_id BINARY(16) NULL,
  payload JSON NOT NULL,             -- items, totals, pricing
  created_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  KEY idx_cart_customer (tenant_id, customer_id),
  CONSTRAINT fk_cart_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  customer_id BINARY(16) NULL,
  currency CHAR(3) NOT NULL,
  status ENUM('pending','confirmed','paid','fulfilled','cancelled','refunded') NOT NULL,
  subtotal_cents BIGINT NOT NULL,
  discount_cents BIGINT NOT NULL DEFAULT 0,
  tax_cents BIGINT NOT NULL DEFAULT 0,
  shipping_cents BIGINT NOT NULL DEFAULT 0,
  total_cents BIGINT NOT NULL,
  shipping_address JSON NOT NULL,
  billing_address JSON NOT NULL,
  placed_at DATETIME(6) NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  CONSTRAINT chk_totals CHECK (total_cents = subtotal_cents - discount_cents + tax_cents + shipping_cents),
  KEY idx_orders_customer (tenant_id, customer_id),
  KEY idx_orders_status_created (tenant_id, status, created_at),
  CONSTRAINT fk_orders_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE order_items (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  order_id BINARY(16) NOT NULL,
  sku VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  qty INT NOT NULL,
  unit_price_cents BIGINT NOT NULL,
  tax_rate DECIMAL(5,2) NOT NULL,
  total_cents BIGINT NOT NULL,
  created_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  KEY idx_items_order (tenant_id, order_id),
  CONSTRAINT fk_items_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_items_order FOREIGN KEY (tenant_id, order_id) REFERENCES orders(tenant_id, id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### 1.5 Payments & Refunds

#### Payments
```sql
CREATE TABLE payments (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  order_id BINARY(16) NOT NULL,
  provider VARCHAR(32) NOT NULL,     -- stripe, mollie, etc.
  status ENUM('pending','authorized','captured','failed','refunded') NOT NULL,
  amount_cents BIGINT NOT NULL,
  currency CHAR(3) NOT NULL,
  provider_ref VARCHAR(128) NULL,
  raw_payload JSON NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  KEY idx_pay_order (tenant_id, order_id),
  CONSTRAINT fk_pay_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pay_order FOREIGN KEY (tenant_id, order_id) REFERENCES orders(tenant_id, id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE refunds (
  tenant_id BINARY(16) NOT NULL,
  id BINARY(16) NOT NULL,
  payment_id BINARY(16) NOT NULL,
  amount_cents BIGINT NOT NULL,
  reason VARCHAR(128) NULL,
  created_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, id),
  KEY idx_ref_payment (tenant_id, payment_id),
  CONSTRAINT fk_ref_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ref_payment FOREIGN KEY (tenant_id, payment_id) REFERENCES payments(tenant_id, id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE webhook_events (
  tenant_id BINARY(16) NOT NULL,
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  provider VARCHAR(32) NOT NULL,
  event_type VARCHAR(128) NOT NULL,
  signature VARCHAR(255) NULL,
  payload JSON NOT NULL,
  received_at DATETIME(6) NOT NULL,
  processed_at DATETIME(6) NULL,
  UNIQUE KEY uniq_provider_sig (tenant_id, provider, signature),
  KEY idx_webhook_received (tenant_id, received_at),
  CONSTRAINT fk_webhooks_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

### 1.6 Pricing & Promotions

#### Coupons
```sql
CREATE TABLE coupons (
  tenant_id BINARY(16) NOT NULL,
  code VARCHAR(32) NOT NULL,
  type ENUM('percent','fixed') NOT NULL,
  value DECIMAL(10,2) NOT NULL,
  max_uses INT NULL,
  used_count INT NOT NULL DEFAULT 0,
  valid_from DATETIME(6) NULL,
  valid_to DATETIME(6) NULL,
  conditions JSON NULL,
  created_at DATETIME(6) NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  PRIMARY KEY (tenant_id, code),
  KEY idx_coupon_validity (tenant_id, valid_from, valid_to),
  CONSTRAINT fk_coupons_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

### 1.7 Observability & Audit

#### Outbox Events
```sql
CREATE TABLE outbox_events (
  tenant_id BINARY(16) NOT NULL,
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  type VARCHAR(128) NOT NULL,          -- e.g., OrderConfirmed
  payload JSON NOT NULL,
  occurred_at DATETIME(6) NOT NULL,
  published_at DATETIME(6) NULL,
  KEY idx_outbox_unpub (tenant_id, published_at, occurred_at),
  CONSTRAINT fk_outbox_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  tenant_id BINARY(16) NOT NULL,
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  actor_id BINARY(16) NULL,
  action VARCHAR(64) NOT NULL,
  entity VARCHAR(64) NOT NULL,
  entity_id BINARY(16) NULL,
  details JSON NULL,
  created_at DATETIME(6) NOT NULL,
  KEY idx_audit_entity (tenant_id, entity, entity_id),
  KEY idx_audit_created (tenant_id, created_at),
  CONSTRAINT fk_audit_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

## Advanced MySQL Techniques

- **Covering indexes** on hottest queries (e.g., order list by status, created_at)
- **Generated columns** to index JSON attributes (shown above)
- **Row-level locking** for inventory (`SELECT … FOR UPDATE`)
- **Partitioning**: monthly partitions for `audit_logs`, `outbox_events`, `webhook_events` (avoid for OLTP tables unless needed)
- **Online schema changes**: gh-ost or pt-osc for zero-downtime migrations
- **Read scaling**: replicas for read-heavy endpoints; Laravel read/write connections
- **Backups/PITR**: daily full + binlogs

- **Tenant-first indexes**: make `tenant_id` the leading column of composite indexes and unique keys so MySQL can prune by tenant quickly.
- **FK pairs**: child FKs include `(tenant_id, <id>)` to guarantee cross-tenant isolation at the database level.

---

## 2. MongoDB (Auxiliary, Flexible)

Use MongoDB where schema changes frequently or where denormalized, editorial data shines.

### 2.1 Reviews Collection

```javascript
// reviews
{
"_id": "rev_ulid",
"product_id": "ulid",        // reference to MySQL product.id (store as string ULID)
"user_id": "ulid|hash",
"rating": 1,
"content": "text",
"status": "pending|approved|rejected",
"metadata": { "ip": "...", "lang": "de" },
"created_at": ISODate("..."),
"updated_at": ISODate("...")
}
```

**Indexes**: 
- `{ product_id: 1, status: 1, created_at: -1 }`
- `{ user_id: 1 }` (optional)

### 2.2 Content Blocks Collection

```javascript
// content_blocks
{
"_id": "cnt_ulid",
"product_id": "ulid",
"locale": "de-DE",
"blocks": [
{ "type": "hero", "data": { "headline": "...", "media": "s3://..." } },
{ "type": "spec_table", "data": { "rows": [["Heel drop","8mm"], ["Stack","30mm"]] } }
],
"version": 3,
"published": true,
"published_at": ISODate("...")
}
```

**Indexes**: `{ product_id: 1, locale: 1, published: 1 }`

### 2.3 Denormalized Read Models (Optional)

Pre-join common PDP data (product + variant highlights + availability snapshot) for super-fast reads:

```javascript
// read_models.pdp_view
{
  "_id": "sku_or_product_ulid",
  "product_id": "ulid",
  "slug": "nike-air-zoom",
  "title": "Nike Air Zoom",
  "brand": "Nike",
  "price_cents": 9999,
  "currency": "EUR",
  "availability": "in_stock",
  "facets": { "color": "black", "size": 42 },
  "seo": { "title": "...", "desc": "..." },
  "updated_at": ISODate("...")
}
```

**Indexes:**
- `{ slug: 1 }`
- `{ "facets.color": 1, "facets.size": 1 }`

**Projection Flow:** Write to MySQL → insert `outbox_event` → background Laravel job consumes outbox → updates MongoDB (reviews unaffected) and any search index. This avoids dual-write inconsistency.

---

## 3. Redis (Ephemeral & Fast)

**Key Patterns:**
- **`cart:{userOrAnonId}`** → hash/list; TTL 7–14d; snapshot to MySQL occasionally
- **`stock_resv:{sku}:{orderId}`** → reservation TTL (e.g., 10–15 min) to prevent oversell
- **`idem:{key}`** → idempotency hash with 24h TTL
- **`ratelimit:{route}:{ip}`** → token bucket counters
- **`events:integration`** (Stream) → async consumers (emails, indexer, analytics)

---

## 4. Reference Query Patterns (Performance)

**Optimization Strategies:**
- **Product listing:** use generated columns (color, size) + compound indexes; paginate with id/created_at keyset (seek method) instead of OFFSET
- **Order history:** compound index (customer_id, created_at DESC); keyset pagination
- **Inventory check:** cache `inventory:{sku}` in Redis, invalidate on movements/reservations
- **Search:** push to OpenSearch/Meilisearch for full-text; MySQL remains source of truth

## Why ULIDs with BINARY(16)?

**1. Why not AUTO_INCREMENT?**
   - Sharding & distributed systems: auto-increment requires a central sequence; harder to scale if you want multi-writer or sharded databases later.
   - Guessable IDs: sequential integers make it easy to scrape or guess records (not great in multi-tenant APIs).
   - Hotspotting: in very high write throughput, sequential PKs can cause contention on the last index page (though InnoDB is optimized for this).

---

**2. Why not CHAR(36) UUID?**
   - UUID v4 (random) stored as CHAR(36) is textual and 4× larger than necessary (36 bytes vs 16 bytes).
   - Text comparison is slower than binary comparison.
   - Index size is bigger, which impacts cache usage and query performance.

---

**3. Why BINARY(16)?**
   - Space efficient: exactly 16 bytes, the native binary representation of a UUID/ULID.
   - Index efficient: smaller index pages → more rows per page → faster lookups.
   - Universally unique: can generate in any app server without DB coordination.
   - ULID friendly: ULIDs are time-sortable, which gives you the nice ordering property (new rows appended in chronological order, avoiding random index writes like UUID v4). This mitigates InnoDB page fragmentation issues.
