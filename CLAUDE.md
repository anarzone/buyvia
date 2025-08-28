# Laravel Monolith Architecture Reference

This document summarises the folder structure and database architecture for a Laravel 12 **monolith** application.  The goal is to keep things clean and scalable without adopting Domain‑Driven Design (DDD) or splitting the code into multiple modules.  Most of the logic remains in a single Laravel application, but we structure the code in a way that encourages clear separation of responsibilities and makes room for growth.

## Folder structure

The application follows Laravel's standard conventions while adding a few extra directories for clarity:

```
app/
  Console/
    Commands/
  Exceptions/
  Http/
    Controllers/
      Auth/
      Catalog/
      Inventory/
      Orders/
      Payments/
      Cart/
      Checkout/
      Fulfillment/
      Notifications/
    Middleware/
    Requests/            # FormRequest validators
    Resources/           # API Resources (transformers) if you use them
  Models/                # Eloquent models
  Services/              # Application services / use cases (thin, task-focused)
  Actions/               # Small, reusable action classes (optional)
  DTOs/                  # Data Transfer Objects (request/response/internal)
  Policies/
  Events/
  Listeners/
  Jobs/                  # Queued jobs (emails, indexing, etc.)
  Rules/                 # Custom validation rules
  Support/               # Helpers, Value Objects (Money, Ids), utilities
  Providers/             # Service providers (AppServiceProvider, etc.)
bootstrap/
config/
database/
  factories/
  migrations/
  seeders/
public/
resources/
  lang/
  views/                 # if you add Blade later
  js/
  sass/
routes/
  api.php
  web.php
  channels.php
  console.php
storage/
  app/
  framework/
  logs/
tests/
  Feature/
  Unit/
vendor/
```

### Notes

- **Controllers** are grouped by feature (e.g. `Http/Controllers/Catalog/ProductsController.php`).  They remain thin and delegate business logic to services.
- **Services** contain the core application logic and orchestrate database updates, caching, queueing and external API calls.  They are easier to test in isolation.
- **DTOs** provide typed objects for moving data between controllers, services and models.
- **Jobs** encapsulate asynchronous work such as sending emails, indexing search documents or publishing outbox events.
- **Support** holds helper classes (money value objects, ULID generators, etc.).

## Database architecture

### 1. MySQL (source of truth)

The primary relational database uses MySQL 8 with the following conventions:

- **Engine:** InnoDB with strict SQL modes (e.g. `STRICT_ALL_TABLES`) and `utf8mb4_0900_ai_ci` collation.
- **Primary keys:** 16‑byte ULIDs stored as `BINARY(16)`.  ULIDs are lexically sortable and avoid the randomness of UUIDv4, which can fragment clustered indexes.  Use Laravel's `HasUlids` trait for automatic generation.
- **Timestamps:** Use `DATETIME(6)` for microsecond precision.
- **Currency:** Represent money as `*_cents BIGINT` and store currency codes (`CHAR(3)`), which avoids floating‑point rounding issues.
- **Flexible attributes:** Use `JSON` columns to store variable attributes on products and variants.  Because JSON columns cannot be indexed directly, define **generated columns** that extract scalar values from JSON and index those generated columns.  The MySQL manual explains that to index JSON you must define a generated column and create an index on it [oai_citation:0‡dev.mysql.com](https://dev.mysql.com/doc/refman/8.4/en/create-table-secondary-indexes.html#:~:text=,Provide%20a%20JSON%20Column%20Index); this technique allows queries to use indexes when filtering JSON data [oai_citation:1‡dev.mysql.com](https://dev.mysql.com/doc/refman/8.4/en/create-table-secondary-indexes.html#:~:text=,Provide%20a%20JSON%20Column%20Index).

#### Catalog tables

- **`products`:** Contains common attributes like `title`, `brand`, `status`, and a `JSON` column for arbitrary attributes (`attributes`).  Generated columns (e.g. `color`, `size`) extract specific values from the JSON and are indexed for fast filtering.
- **`product_variants`:** Linked to `products` via `product_id`.  Stores per‑variant SKUs, pricing, barcode, weight and a `JSON` attribute (`attr`) with generated columns for variant facets.
- **`categories`, `product_category`:** Support hierarchical categories with a self‑referencing `parent_id` and a many‑to‑many bridge table.
- **`product_media`:** Holds product media URLs and positions.

#### Customers and addresses

- **`customers`:** Stores customer information (email, name, phone).
- **`addresses`:** Stores address payloads in a JSON column.  One address can be billing or shipping and is associated with a `customer_id`.

#### Inventory management

- **`inventory_levels`:** Tracks `on_hand` stock and `reserved` quantities per SKU and location.  Use `SELECT … FOR UPDATE` to lock rows when reserving stock.  A reservation reduces `reserved` and is stored in Redis with a TTL for fast checks.
- **`inventory_reservations`:** Records reservations with an expiry timestamp (`expires_at`).  The application releases reservations when they expire (via scheduled job).
- **`inventory_movements`:** Provides an audit trail of stock adjustments (restocks, shipments, returns).  Use auto‑increment `id` for ordering.

#### Cart, orders and checkout

- **Redis** holds active carts (`cart:{user}`) as hashes/lists with a TTL of a week or two.  **MySQL** stores snapshots of carts for analytics/recovery in `cart_snapshots` (optional).
- **`orders`:** Represents an order aggregate with totals broken down by `subtotal_cents`, `discount_cents`, `tax_cents`, `shipping_cents` and `total_cents` (with a `CHECK` constraint).  Includes status (`pending`, `confirmed`, `paid`, `fulfilled`, `cancelled`, `refunded`) and stores billing/shipping addresses as JSON.
- **`order_items`:** Holds the items linked to an order.  Stores item name, SKU, quantity, unit price and tax rate; can be denormalised to avoid joining `products` during order retrieval.
- **`payments` / `refunds`:** Track payment intents and refunds.  Store the raw provider payload for auditing.  Payments are associated with orders and have statuses (`pending`, `authorized`, `captured`, `failed`, `refunded`).  Refunds link to payments and record refunded amounts and reasons.
- **`webhook_events`:** Persist raw webhook events from payment providers with signatures.  This allows auditing and replay; the app verifies signatures before processing.

#### Pricing and promotions

- **`coupons`:** Simple promotions table storing code, type (`percent` or `fixed`), value, usage limits and optional conditions (JSON).  Index validity ranges on `valid_from` and `valid_to`.

#### Observability and integration

- **`outbox_events`:** Implements the [outbox pattern](https://microservices.io/patterns/data/transactional-outbox.html).  Whenever the application needs to publish an integration event (e.g. `OrderConfirmed`), it writes a row in the outbox inside the same transaction.  A background job reads unpublished rows and publishes them to Redis Streams or another message broker.  Use an index on `(published_at, occurred_at)` to efficiently fetch unpublished events.
- **`audit_logs`:** Generic audit table that records user actions and changes.  Index on `(entity, entity_id)` for filtering logs per object.

##### Advanced techniques

- **Generated columns and JSON indexing:** As referenced in the MySQL documentation, a JSON column cannot be indexed directly.  MySQL encourages creating a generated column that extracts the value to be indexed and then indexing that column [oai_citation:2‡dev.mysql.com](https://dev.mysql.com/doc/refman/8.4/en/create-table-secondary-indexes.html#:~:text=,Provide%20a%20JSON%20Column%20Index).
- **Row‑level locking:** Use `SELECT … FOR UPDATE` on `inventory_levels` when reserving stock to prevent overselling.
- **Partitioning:** For large append‑only tables (`audit_logs`, `outbox_events`, `webhook_events`), consider partitioning by date to improve maintenance and purge old data.
- **Read scaling:** Use replicas for read‑heavy queries; Laravel supports separate read/write database connections.

### 2. MongoDB (flexible, secondary)

MongoDB stores unstructured or rapidly changing data that doesn't fit well into the relational model:

- **Reviews:** A `reviews` collection stores product reviews with fields like `product_id` (string ULID referencing a MySQL product), `user_id`, `rating` (1–5), `content`, `status` (pending/approved/rejected) and a `metadata` object (IP, language).  Index on `(product_id, status, created_at)` to support moderation queries.
- **Product content blocks:** A `content_blocks` collection holds editorial content for product pages.  Each document references a `product_id`, `locale`, an array of blocks (hero images, specification tables, etc.), a `version` number and a published flag (`published`).  Index on `(product_id, locale, published)`.
- **Denormalised read models (optional):** To serve product detail pages without joining across multiple tables, create a `pdp_view` collection that pre‑joins MySQL product/variant data, inventory availability and SEO fields.  These documents are updated by consuming events from the outbox.

### 3. Redis (ephemeral)

Redis is used for fast, in‑memory operations:

- **Carts:** Store active shopping carts under keys like `cart:{user_or_anon_id}`.  Use hashes or lists to represent items.  Set TTLs (e.g. 7–14 days) and optionally persist snapshots to MySQL.
- **Inventory reservations:** Maintain keys like `stock_resv:{sku}:{orderId}` with a TTL (e.g. 10 – 15 minutes) to prevent overselling when multiple users attempt to check out the same item.  When a reservation expires, a scheduled job releases the reserved quantity in MySQL.
- **Idempotency keys:** Record POST/PUT requests under `idem:{hash}` so that repeated requests with the same idempotency key return the original response instead of performing duplicate actions.
- **Rate limiting:** Use keys like `ratelimit:{route}:{ip}` with counters and TTLs to enforce per‑route rate limits.
- **Lightweight message queues:** Optionally use Redis Streams (`events:integration`) to publish integration events (e.g. for sending emails or updating search indexes).  Consumers should track the last processed ID and be idempotent.

## Summary

This architecture keeps a single Laravel application easy to reason about while providing room for scalability:

- **MySQL** holds all authoritative transactional data and uses advanced features (ULIDs, JSON columns, generated columns, row locks, outbox pattern).
- **MongoDB** handles unstructured content (reviews and CMS blocks) and optional denormalised read models.
- **Redis** serves fast operations (carts, reservations, idempotency, rate limiting, lightweight queues).

The folder structure sticks closely to Laravel conventions but introduces `Services` and `DTOs` directories to separate concerns and keep controllers thin.  This document should serve as a reference when implementing features or performing migrations.