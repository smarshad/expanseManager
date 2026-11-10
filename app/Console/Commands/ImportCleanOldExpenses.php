<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseFor;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCleanOldExpenses extends Command
{
    protected $signature = 'expenses:import-clean-old
        {--file=database/data/june_clean_import.json : Cleaned JSON file path}
        {--user_id=20 : User ID for imported records}
        {--from-date=2026-06-01 : Import from date Y-m-d}
        {--to-date=2026-06-31 : Import to date Y-m-d}
        {--default-account=HDFC : Default account name if missing}
        {--default-vendor=Other : Default vendor/payee if missing}
        {--dry-run : Preview only, do not insert}
        {--preview=30 : Number of rows to show in dry-run}';

    protected $description = 'Import cleaned old expenses JSON into the new expense structure with date filter, account, platform and cashback support.';

    public function handle(): int
    {
        $file = base_path($this->option('file'));

        if (! file_exists($file)) {
            $this->error("JSON file not found: {$file}");
            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);

        if (! is_array($rows)) {
            $this->error('Invalid JSON file.');
            return self::FAILURE;
        }

        $userId = (int) $this->option('user_id');
        $fromDate = (string) $this->option('from-date');
        $toDate = (string) $this->option('to-date');
        $defaultAccount = (string) $this->option('default-account');
        $defaultVendor = (string) $this->option('default-vendor');
        $preview = (int) $this->option('preview');

        if (! User::whereKey($userId)->exists()) {
            $this->error("User not found: {$userId}");
            return self::FAILURE;
        }

        $rows = collect($rows)
            ->filter(fn ($row) => ($row['expense_date'] ?? null) >= $fromDate && ($row['expense_date'] ?? null) <= $toDate)
            ->values();

        $ramadanCount = $rows->filter(fn ($row) => ($row['parent_category'] ?? '') === 'Ramadan Expense')->count();
        $cashbackTotal = $rows->sum(fn ($row) => (float) ($row['cashback'] ?? 0));

        $this->info('Rows found: '.$rows->count());
        $this->info("Date range: {$fromDate} to {$toDate}");
        $this->info("Ramadan mapped rows: {$ramadanCount}");
        $this->info('Cashback total: Rs. '.number_format($cashbackTotal, 2));

        if ($this->option('dry-run')) {
            $this->table(
                ['Old No', 'Date', 'Category', 'Sub Category', 'For', 'Method', 'Platform', 'Amount', 'Cashback', 'Description'],
                $rows->take($preview)->map(function ($row) {
                    return [
                        $row['old_serial'] ?? '',
                        $row['expense_date'] ?? '',
                        $row['parent_category'] ?? '',
                        $row['sub_category'] ?? '',
                        $row['expense_for'] ?? '',
                        $row['payment_method'] ?? '',
                        $row['payment_platform'] ?? '',
                        $row['amount'] ?? '',
                        $row['cashback'] ?? 0,
                        Str::limit($row['description'] ?? '', 35),
                    ];
                })->toArray()
            );

            $this->warn('Dry run only. No data inserted. Remove --dry-run to import.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows, $userId, $defaultAccount, $defaultVendor) {
            foreach ($rows as $row) {
                $parent = $this->category($row['parent_category'], null, $userId, $row['nature'] ?? 'need');
                $category = $this->category($row['sub_category'], $parent->id, $userId, $row['nature'] ?? 'need');

                $expenseFor = ExpenseFor::firstOrCreate(
                    ['name' => $row['expense_for'] ?? 'Personal'],
                    ['is_active' => true, 'created_by' => $userId]
                );

                $paymentMethod = PaymentMethod::firstOrCreate(
                    ['name' => $row['payment_method'] ?? 'UPI'],
                    ['is_active' => true, 'created_by' => $userId]
                );

                $accountId = $this->accountId($row['account'] ?? $defaultAccount, $defaultAccount);
                $platformId = $this->platformId($row['payment_platform'] ?? null);

                $vendor = Vendor::firstOrCreate(
                    ['user_id' => $userId, 'name' => $row['vendor_name'] ?? $defaultVendor],
                    ['vendor_type' => 'Other', 'is_active' => true]
                );

                Expense::create([
                    'description' => $row['description'] ?? '-',
                    'amount' => $row['amount'],
                    'transaction_kind' => $row['transaction_kind'] ?? 'expense',
                    'spending_nature' => $row['spending_nature'] ?? 'unclassified',
                    'recurrence' => $row['recurrence'] ?? 'none',
                
                    'category_id' => $category->id,
                    'expense_for_id' => $expenseFor->id,
                    'payment_method_id' => $paymentMethod->id,
                    'account_id' => $accountId,
                    'payment_platform_id' => $platformId,
                    'vendor_id' => $vendor?->id,
                
                    'cashback' => $row['cashback'] ?? 0,
                    'notes' => $row['notes'] ?? null,
                    'expense_date' => $row['expense_date'],
                    'created_by' => $userId,
                
                    'is_recurring' => (bool) ($row['is_recurring'] ?? false),
                    'frequency' => $row['frequency'] ?? 'occasionally',
                    'nature' => $row['nature'] ?? 'need',
                ]);
            }
        });

        $this->info('Import completed successfully.');
        return self::SUCCESS;
    }

    private function category(string $name, ?int $parentId, int $userId, string $nature = 'need'): Category
    {
        return Category::firstOrCreate(
            ['name' => $name, 'parent_id' => $parentId],
            [
                'slug' => Str::slug($name),
                'is_active' => true,
                'nature' => in_array($nature, ['need', 'want', 'investment'], true) ? $nature : 'need',
                'budget_type' => $name === 'Ramadan Expense' ? 'soft_budget' : 'soft_budget',
                'created_by' => $userId,
            ]
        );
    }

    private function accountId(?string $name, string $defaultAccount): ?int
    {
        $name = $name ?: $defaultAccount;

        $id = DB::table('accounts')->where('name', $name)->value('id');
        if ($id) {
            return (int) $id;
        }

        return DB::table('accounts')->where('name', $defaultAccount)->value('id');
    }

    private function platformId(?string $name): ?int
    {
        if (! $name || ! DB::getSchemaBuilder()->hasTable('payment_platforms')) {
            return null;
        }

        $id = DB::table('payment_platforms')->where('name', $name)->value('id');
        return $id ? (int) $id : null;
    }
}
