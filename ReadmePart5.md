# Feature Implementation: PaymentMethod CRUD

This document outlines the professional, layered implementation of a full Create, Read, Update, Delete (CRUD) feature for the `PaymentMethod` entity. The implementation strictly adheres to the Service/Repository pattern, utilizes Data Transfer Objects (DTOs), and leverages standardized components like the Base Controller for consistency and maintainability.

## 1. Architectural Foundation

The first steps establish the core components necessary for a clean, layered architecture.

### 1.1. Model, Migration, and Controller

Generate the foundational files using a single command:

```bash
php artisan make:model PaymentMethod -mc
```

Define the database schema in the generated migration file. Note the use of `unique()` for the name and the inclusion of `created_by` for auditing.

```php
// database/migrations/..._create_payment_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code');
            $table->bigInteger('created_by'); // Corrected from created_bu
            $table->softDeletes();
            $table->timestamps();
        });
    }
    // ...
};
```

### 1.2. Data Transfer Object (DTO)

The DTO ensures data integrity and consistency when passing data between the Controller and the Service layer.

```bash
php artisan make:dto PaymentMethodDTO
```

### 1.3. API Resource Transformation

Create a Resource to standardize the output format for API responses, ensuring data is consistently presented to the frontend.

```bash
php artisan make:resource PaymentMethodResource
```

### 1.4. Service and Repository Pattern

Implement the core business logic and data access layers, enforcing the separation of concerns.

| Layer | Purpose | Command/File |
| :--- | :--- | :--- |
| **Interface** | Defines the data access contract. | `php artisan make:interface PaymentMethodRepositoryInterface` |
| **Repository** | Implements the data access logic (Eloquent queries). | `touch app/Repositories/PaymentMethodRepository.php` |
| **Service** | Implements the business logic (validation, transactions, caching). | `php artisan make:service PaymentMethodService` |

***Action Required:*** Ensure the Service Provider (e.g., `AppServiceProvider`) binds the interface to the concrete repository implementation.

### 1.5. Form Requests (Server-Side Validation)

Use dedicated Form Request classes for robust, server-side validation.

```bash
php artisan make:request PaymentMethod/StoreRequest && php artisan make:request PaymentMethod/UpdateRequest
```

## 2. Database and Configuration

### 2.1. Run Migrations

Execute the migration to create the new table:

```bash
php artisan migrate
```

### 2.2. Standardized Pagination Configuration

Centralize the default pagination size for consistency across the application.

```bash
touch config/pagination.php
```

**File Content (`config/pagination.php`):**

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Default Pagination Size
    |--------------------------------------------------------------------------
    */
    'default' => env('PAGINATION_PER_PAGE', 15),
];
```

***Usage:*** Use `config('pagination.default')` in your Service layer instead of hardcoding the value.

### 2.3. Seeder

Create a seeder for initial data population and testing purposes.

```bash
php artisan make:seeder PaymentMethodSeeder
```

## 3. Controller Implementation and Standardization

The `PaymentMethodController` must extend the previously established `BaseController` to inherit standardized exception handling and JSON response methods.

### 3.1. Update Controller

The controller's methods should focus on:
1.  Receiving the request (validated by Form Requests).
2.  Mapping the request data to the DTO.
3.  Calling the appropriate method on the `PaymentMethodService`.
4.  Returning the result using the inherited `successResponse()` or catching exceptions using inherited handlers like `handleValidationException()`.

### 3.2. Routing

Define the necessary routes for the CRUD operations (API and Web views).

```php
// routes/web.php or routes/api.php
// Example for API resource routes
Route::resource('payment-methods', PaymentMethodController::class)->except(['create', 'edit']);
// Example for view routes
Route::get('payment-methods/list', [PaymentMethodController::class, 'index'])->name('paymentmethod.list');
// ...
```

## 4. Frontend Integration

### 4.1. Language Files

Create necessary localization strings for the new feature.

### 4.2. UI Part and Menu Integration

Integrate the new feature into the application's navigation structure.

### 4.3. JavaScript Logic

Create the dedicated JavaScript file for managing the frontend interactions (e.g., AJAX calls, data table initialization, form submission).

```bash
touch public/js/manage-paymentmethod.js
```

## Summary

By following this layered approach, the `PaymentMethod` feature is implemented with maximum separation of concerns, high testability, and consistent error handling, aligning with professional software development standards.