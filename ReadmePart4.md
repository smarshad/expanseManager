
## Day 8 CRUD ExpenseType
---

## Phase 1: Foundational Setup (Migration, Controller, Model, Views)

Create Migration
Update Model (add fillable accordingly and in protected static function boot() create/update slug)

## Phase 2: Valaidate data

Create Form Request (Store / Update)

## Phase 3: Create Data transfer object

Create DTOs

## Phase 4: Create Service Layer

create 
    app/Interfaces/CategoryRepositoryInterface.php
    app/Repositories/CategoryRepository.php
    app/Services/CategoryService.php

## Phase 5: Create resource (response)

    app/Http/Resources/CategoryResource.php

## Phase 6: UI

    Create common modal 
    resources/views/admin/includes/modal.blade.php
    resources/views/admin/category/index.blade.category

    Create manage-category.js
    use datable with pagiantion
    refactor error code in ajax

## Phase 7: Seeder
    Create Expense type Seeder

## extra 
## Create BaseController (for centralised log)
## Refactor ExpenseTypeController/ExpenseTypeService/ExpenseTypeRepositoryInterface
