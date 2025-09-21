# Buyvia - Multi-tenant E-commerce Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/Redis-7.0-DC382D?style=for-the-badge&logo=redis" alt="Redis 7.0">
</p>

## About Buyvia

Buyvia is a modern, multi-tenant e-commerce platform built with Laravel 12. It's designed as a monolithic application with clean architecture principles, providing a scalable foundation for SaaS e-commerce solutions.

### 🏗️ Architecture Highlights

- **Multi-tenant SaaS architecture** with tenant isolation at database level
- **Composite primary keys** for robust data separation
- **Service layer pattern** for clean business logic separation
- **DTO (Data Transfer Objects)** for type-safe data handling
- **Redis-based shopping carts** for high performance
- **Event-driven architecture** with outbox pattern for integrations

### 🚀 Current Status: Phase 1 - Database Foundation Complete

✅ **Completed:**
- Multi-tenant database schema with composite keys
- 23 database migrations with proper relationships
- Core models: Products, Variants, Categories, Customers, Orders, Inventory
- Composite key relationships using Compoships package
- ULID-based primary keys for distributed-friendly IDs

🚧 **In Progress:**
- Foundation architecture (Services, DTOs, Controllers)
- API endpoint structure
- Multi-tenancy middleware and authentication

## 📋 Features

### Core E-commerce Functionality
- **Product Management** - Products, variants, categories with hierarchical support
- **Inventory Management** - Real-time stock tracking, reservations, movements
- **Customer Management** - Customer profiles, addresses, order history
- **Order Processing** - Order lifecycle management, payment tracking
- **Multi-tenant Architecture** - Complete data isolation between tenants

### Technical Features
- **Composite Primary Keys** - Robust multi-tenant data separation
- **ULID Primary Keys** - Time-ordered, URL-safe identifiers
- **Event Sourcing Ready** - Outbox pattern for reliable event publishing
- **Redis Integration** - High-performance cart and session management
- **Audit Logging** - Complete action and change tracking
- **Webhook Support** - External service integrations

## 🛠️ Technology Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL 8.0 with InnoDB
- **Cache/Queue:** Redis 7.0
- **Search:** Elasticsearch (planned)
- **Files:** MongoDB (for unstructured content)
- **Authentication:** Laravel Sanctum

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0
- Redis 7.0
- Node.js (for asset compilation)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone [repository-url]
   cd Buyvia
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   - Update `.env` with your MySQL credentials
   - Update `.env` with your Redis credentials

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Install Node dependencies** (when frontend is added)
   ```bash
   npm install
   npm run build
   ```

## 🗄️ Database Architecture

### Multi-tenant Design
All tenant-scoped tables use composite primary keys with `tenant_id`:
- `products` - (`tenant_id`, `id`)
- `product_variants` - (`tenant_id`, `id`)
- `categories` - (`tenant_id`, `id`)
- `customers` - (`tenant_id`, `id`)
- `orders` - (`tenant_id`, `id`)
- `inventory_levels` - (`tenant_id`, `sku`, `location_id`)

### Key Features
- **Composite Foreign Keys** - Proper referential integrity across tenants
- **Generated Columns** - JSON attribute indexing for fast queries
- **Soft Deletes** - Data retention with logical deletion
- **Timestamps** - Microsecond precision tracking

## 🏗️ Architecture

### Directory Structure
```
app/
├── Http/Controllers/     # Thin controllers by domain
│   ├── Catalog/         # Product management
│   ├── Inventory/       # Stock management
│   ├── Orders/          # Order processing
│   └── Payments/        # Payment handling
├── Services/            # Business logic layer
├── DTOs/                # Data transfer objects
├── Models/              # Eloquent models
├── Policies/            # Authorization logic
└── Support/             # Utilities and value objects
```

### Design Patterns
- **Service Layer** - Centralized business logic
- **Repository Pattern** - Data access abstraction
- **DTO Pattern** - Type-safe data transfer
- **Event Sourcing** - Audit trail and integrations

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

Run specific test types:
```bash
# Unit tests
php artisan test --testsuite=Unit

# Feature tests
php artisan test --testsuite=Feature
```

## 📈 Development Roadmap

### Phase 1: Foundation Architecture ✅
- Database schema and migrations
- Core models with composite keys
- Basic relationships

### Phase 2: Service Layer 🚧
- Business logic services
- DTO implementation
- Controller structure

### Phase 3: Authentication & Multi-tenancy
- Tenant resolution middleware
- API authentication
- Authorization policies

### Phase 4: Core Features
- Product catalog management
- Inventory tracking
- Customer management
- Basic order processing

### Phase 5: Advanced Features
- Payment processing
- Advanced inventory
- Reporting & analytics
- Search functionality

## 📚 Documentation

- **[CLAUDE.md](./CLAUDE.md)** - Architecture guidelines and conventions
- **[PLANNING.md](./PLANNING.md)** - Development sessions and decisions log
- **API Documentation** - Coming soon

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔧 Support

For support and questions:
- Create an issue in this repository
- Check the documentation in `CLAUDE.md`
- Review development notes in `PLANNING.md`
