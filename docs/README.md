# Old Expense Clean Import Package

Files:

- `database/data/old_expense_import_rules.json`
- `database/data/old_expenses_feb_2026_cleaned.json`
- `app/Console/Commands/ImportCleanOldExpenses.php`

Important rule:
Any row where category, description, or notes contains `iftaar`, `iftar`, `iftaari`, `iftari`, `sehri`, `ramadan`, or `roza`
is mapped to:

- Parent Category: `Ramadan Expense`
- Sub Category: `Iftaar` or `Sehri`

Commands:

```bash
php artisan optimize:clear
php artisan expenses:import-clean-old --file=database/data/old_expenses_feb_2026_cleaned.json --user_id=20 --dry-run
php artisan expenses:import-clean-old --file=database/data/old_expenses_feb_2026_cleaned.json --user_id=20
```

If your `expenses` table does not have `legacy_serial_no`, add it:

```bash
php artisan make:migration add_legacy_serial_no_to_expenses_table --table=expenses
```

```php
Schema::table('expenses', function (Blueprint $table) {
    $table->unsignedBigInteger('legacy_serial_no')->nullable()->index()->after('id');
});
```
