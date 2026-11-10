# Category Hierarchy Code Changes

This package changes categories from a flat list to unlimited parent/sub-category style.

## New category structure

- `categories.parent_id` points to another category.
- Parent example: `Groceries`
- Child example: `Groceries / Vegetables`
- Expense still uses only `expenses.category_id`; select the child category when available.

## Added category fields

- `budget_type`: `no_budget`, `soft_budget`, `hard_budget`, `fixed`
- `monthly_budget`: optional amount
- `nature`: `need`, `want`, `investment`
- `sort_order`: display order

## Added expense fields

- `is_recurring`
- `frequency`: daily, weekly, monthly, quarterly, yearly
- `nature`: optional override for need/want/investment

## Install steps

1. Backup DB.
2. Copy files into Laravel project.
3. Run:

```bash
php artisan migrate
php artisan db:seed --class=PersonalExpenseCategorySeeder
php artisan optimize:clear
```

## Important rule

Do not create unlimited database columns for every subcategory. Keep one `categories` table with `parent_id`. This supports any number of categories/subcategories without future code changes.
