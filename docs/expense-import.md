# Expense CSV / Excel Import

Supported formats: `.csv` and `.xlsx`.

## 1. Prepare the file

Use either template:

- `samples/expense-import-sample.csv`
- `samples/expense-import-sample.xlsx`

Required columns:

- `expense_date`
- `description`
- `amount`
- `category`
- `expense_for`
- `payment_method`
- `account`

Optional columns:

- `cashback`
- `payment_platform`
- `vendor`
- `nature` (`need`, `want`, `investment`)
- `transaction_kind` (`expense`, `investment`, `transfer`, `debt`)
- `frequency` (`daily`, `occasionally`, `weekly`, `monthly`, `quarterly`, `yearly`)
- `notes`

Use the names configured in the application. For subcategories use `Parent / Subcategory`, for example `Groceries / Milk`.

## 2. Dry run (no database changes)

```bash
php8.2 artisan expenses:import samples/expense-import-sample.csv --user=20
php8.2 artisan expenses:import samples/expense-import-sample.xlsx --user=20
```

The command prints valid, duplicate and invalid row counts. If any row is invalid, nothing is inserted.

## 3. Import after reviewing

```bash
php8.2 artisan expenses:import /absolute/path/expenses.xlsx --user=20 --apply
```

Create missing vendors while importing:

```bash
php8.2 artisan expenses:import /absolute/path/expenses.xlsx --user=20 --create-vendors --apply
```

Duplicate detection uses date, description, amount, category and account. To intentionally allow duplicates, add `--allow-duplicates`.
