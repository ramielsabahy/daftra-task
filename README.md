# 📦 Inventory Management System API

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)](https://mysql.com)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-6875F5?style=for-the-badge)](https://laravel.com/docs/sanctum)

A robust, enterprise-grade multi-warehouse inventory management API built with Laravel. This system handles complex inventory logic, multi-warehouse stock transfers with race condition protection, and automated low-stock monitoring.

---

## ✨ Key Features

-   **🏢 Multi-Warehouse Management**: Create, update, and manage multiple warehouse locations.
-   **📦 Item Catalog**: Manage product details, units, and SKU tracking.
-   **📊 Real-time Inventory**: Track stock levels across all warehouses with unified views.
-   **🔄 Atomic Stock Transfers**: Transfer stock between warehouses safely using **Pessimistic Locking** (`SELECT FOR UPDATE`) to prevent double-spending or race conditions.
-   **⚠️ Low Stock Alerts**: Asynchronous monitoring system that triggers events when inventory falls below thresholds.
-   **🛡️ Secure API**: Fully protected by Laravel Sanctum with mobile-ready token-based authentication.
-   **📝 Audit Logging**: Immutable logs for every stock transfer, capturing "before" and "after" snapshots.

---

## 🏗️ Architectural Overview

This project follows a **Service-Repository Pattern** to ensure clean separation of concerns:

-   **Controllers**: Handle HTTP input and output using standard JSON responses.
-   **Services**: House the core business logic (e.g., `StockTransferService`).
-   **Repositories**: Abstract database queries for cleaner, more testable code.
-   **Resources**: Standardize API output format using Laravel Eloquent Resources.
-   **Exceptions**: Custom domain-specific exceptions (e.g., `InsufficientStockException`, `InactiveWarehouseException`).

### Race Condition Protection
The stock transfer logic uses database-level pessimistic locking:
1.  Begin Transaction.
2.  Lock the source inventory row.
3.  Check quantity.
4.  Lock (or create) the destination inventory row.
5.  Perform debit/credit.
6.  Log the transfer.
7.  Commit Transaction.

---

## 🛠️ Tech Stack

-   **Framework**: Laravel 11+
-   **Authentication**: Laravel Sanctum
-   **Database**: MySQL / MariaDB (Supports `CHECK` constraints for non-negative quantities)
-   **Pattern**: Service Layer Architecture
-   **API Standard**: RESTful with JSON:API inspired responses

---

## 🚀 Getting Started

### Prerequisites
-   PHP 8.2 or higher
-   Composer
-   MySQL 8.0+

### Installation

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/ramielsabahy/daftara-task.git
    cd daftara-task
    ```

2.  **Install dependencies**:
    ```bash
    composer install
    ```

3.  **Environment Setup**:
    ```bash
    cp .env.example .env
    ```
    *Update your database credentials in `.env`.*

4.  **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```

5.  **Run Migrations & Seeders**:
    ```bash
    php artisan migrate --seed
    ```

6.  **Start the Server**:
    ```bash
    php artisan serve
    ```

---

## 📖 API Documentation

The API is prefixed with `/api/v1`. All protected routes require a Bearer Token.

### Authentication
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/auth/login` | Returns a Sanctum token |
| `POST` | `/auth/logout` | Revokes the current token |
| `GET` | `/auth/me` | Returns current user details |

### Inventory & Warehouses
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/warehouses` | List all warehouses |
| `GET` | `/items` | List all items |
| `GET` | `/inventory` | View global stock levels |
| `POST` | `/transfers` | Execute a stock transfer |
| `GET` | `/alerts/low-stock` | List triggered low stock events |

*For a full list of parameters and example payloads, please refer to the [Postman Collection](https://documenter.getpostman.com/view/3208343/2sBXqFN2nz).*

---

## 🧪 Testing

The project includes feature tests for the core logic:

```bash
php artisan test
```

Key test areas:
-   Authentication flows.
-   Transfer validation (different warehouses, sufficient stock).
-   Concurrent transfer simulation (mocked locking).
-   Low stock event triggering.

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
