# Laravel E-Commerce API

A simple but production-ready e-commerce REST API built with **Laravel 12** and **Laravel Sanctum**.

Covers authentication, role-based access control, product management, and order processing — designed to follow Laravel best practices and clean code principles.

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+ (or SQLite for testing)

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/yourname/laravel-ecommerce-api.git
cd laravel-ecommerce-api

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate
```

---

## Environment Setup

Open `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

---

## Database Setup

```bash
# Run migrations
php artisan migrate

# Seed with admin account + sample products
php artisan migrate --seed
```

Default admin credentials after seeding:

| Field    | Value             |
| -------- | ----------------- |
| Email    | admin@example.com |
| Password | password          |

---

## Running the API

```bash
php artisan serve
# Available at: http://localhost:8000
```

---

## Running Tests

Tests run on an **SQLite in-memory database** — no setup required.

```bash
php artisan test
```

Expected output: `17 passed (29 assertions)`

---

## API Endpoints

### Authentication

| Method | Endpoint    | Auth   | Description              |
| ------ | ----------- | ------ | ------------------------ |
| POST   | /api/login  | —      | Login, returns API token |
| POST   | /api/logout | Bearer | Revoke current token     |

### Products

| Method | Endpoint           | Auth   | Role   |
| ------ | ------------------ | ------ | ------ |
| GET    | /api/products      | —      | Public |
| GET    | /api/products/{id} | —      | Public |
| POST   | /api/products      | Bearer | Admin  |
| PUT    | /api/products/{id} | Bearer | Admin  |
| DELETE | /api/products/{id} | Bearer | Admin  |

### Orders

| Method | Endpoint         | Auth   | Role   |
| ------ | ---------------- | ------ | ------ |
| POST   | /api/orders      | —      | Public |
| GET    | /api/orders      | Bearer | Admin  |
| GET    | /api/orders/{id} | Bearer | Admin  |

---

## Request & Response Examples

### Login

```
POST /api/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password"
}
```

Response:

```json
{
    "data": {
        "token": "1|abc...",
        "user": {
            "id": 1,
            "name": "Admin",
            "email": "admin@example.com",
            "role": "admin"
        }
    }
}
```

### Create Product (Admin)

```
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Laptop Pro 15",
    "description": "High-performance laptop",
    "price": 15000000,
    "status": "active"
}
```

### Create Order (Public)

```
POST /api/orders
Content-Type: application/json

{
    "customer_name": "Budi Santoso",
    "customer_email": "budi@mail.com",
    "items": [
        { "product_id": 1, "qty": 2 }
    ]
}
```

Response:

```json
{
    "data": {
        "id": 1,
        "customer_name": "Budi Santoso",
        "status": "pending",
        "total_price": 30000000,
        "items": [
            {
                "product_id": 1,
                "product": "Laptop Pro 15",
                "qty": 2,
                "price": 15000000,
                "subtotal": 30000000
            }
        ]
    }
}
```

---

## Business Rules

- **Active products only** — orders with inactive product IDs are rejected with `422`
- **Price snapshot** — `order_items.price` stores the product price at the time of purchase, not a reference
- **Auto-calculated totals** — `subtotal = qty × price`, `total_price` is the sum of all subtotals
- **DB Transaction** — entire order creation is wrapped in a transaction; any failure rolls back completely
- **Role-based access** — admin routes return `403` for regular users

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   └── OrderController.php
│   ├── Middleware/
│   │   └── AdminMiddleware.php
│   ├── Requests/
│   │   ├── Auth/LoginRequest.php
│   │   ├── Product/StoreProductRequest.php
│   │   ├── Product/UpdateProductRequest.php
│   │   └── Order/StoreOrderRequest.php
│   └── Resources/
│       ├── ProductResource.php
│       ├── OrderResource.php
│       └── OrderItemResource.php
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   └── OrderItem.php
database/
├── factories/
├── migrations/
└── seeders/
routes/
└── api.php
tests/Feature/
├── AuthTest.php
├── ProductTest.php
└── OrderTest.php
```
