# February Clean Import Summary

Total rows: 184

Ramadan mapped rows: 23

Fallback/unknown rows: 3

Cashback total: Rs. 108.34

## Rule Counts

- description_rule: 98
- direct_category: 54
- ramadan_override: 23
- fallback: 3
- investment_rule: 2
- cash_withdrawal_rule: 2
- credit_card_bill_rule: 1
- education_rule: 1

## Category Counts

- Ramadan Expense > Ramadan Expense: 23
- Groceries > Milk: 21
- Groceries > General Grocery: 14
- Groceries > Fruits: 9
- Food & Dining > Breakfast: 8
- Medical > Medicines: 8
- Groceries > Vegetables: 8
- Food & Dining > Lunch: 7
- Food & Dining > Dinner: 7
- Food & Dining > Tea: 6
- Utilities > UTS Recharge: 6
- Groceries > Egg: 5
- Groceries > Bakery: 4
- Food & Dining > Snacks: 4
- Household > Water Can: 4
- Food & Dining > Little Joys: 4
- Groceries > Meat: 4
- Shopping > Clothes: 4
- Food & Dining > Coconut Water: 3
- Miscellaneous > Other: 3
- Travel > Rapido: 3
- Household > Kitchen Items: 2
- Groceries > Oil: 2
- Investment > Mutual Fund: 2
- Cash Withdrawal > Cash Withdrawal: 2
- Vehicle > Petrol: 2
- Food & Dining > Juice: 2
- Groceries > Dairy: 2
- Travel > Train: 2
- Personal Care > Haircut: 1
- Shopping > Shoes: 1
- Utilities > Mobile Recharge: 1
- Medical > Eye Care: 1
- Shopping > Toy: 1
- Groceries > Rice: 1
- Medical > Doctor Consultation: 1
- Groceries > Diaper: 1
- Travel > Metro: 1
- Utilities > Credit Card Bill: 1
- Education > Fees: 1
- Personal Care > Cosmetics: 1
- Utilities > Railone Recharge: 1

## Run Commands

```bash
mkdir -p database/data
cp february_clean_import.json database/data/february_clean_import.json
cp ImportCleanOldExpenses.php app/Console/Commands/ImportCleanOldExpenses.php
php8.2 artisan optimize:clear
php8.2 artisan expenses:import-clean-old --user_id=20 --from-date=2026-02-01 --to-date=2026-02-28 --dry-run --preview=50
php8.2 artisan expenses:import-clean-old --user_id=20 --from-date=2026-02-01 --to-date=2026-02-28
```
