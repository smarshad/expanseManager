<?php

namespace App\DTOs;

class ExpenseDTO
{
    public function __construct(
        public int $category_id,
        public int $expense_for_id,
        public int $payment_method_id,
        public int $account_id,
        public ?int $payment_platform_id,
        public ?int $vendor_id,
        public string $expense_date,
        public string $description,
        public float $amount,
        public float $cashback = 0.0,
        public ?string $notes = null,
        public bool $is_recurring = false,
        public ?string $frequency = null,
        public ?string $nature = null,
        public string $transaction_kind = 'expense',
        public ?string $file_path = null,
        public readonly ?int $created_by = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $frequency = $data['frequency'] ?? null;

        return new self(
            category_id: (int) $data['category_id'],
            expense_for_id: (int) $data['expense_for_id'],
            payment_method_id: (int) $data['payment_method_id'],
            account_id: (int) $data['account_id'],
            payment_platform_id: isset($data['payment_platform_id']) ? (int) $data['payment_platform_id'] : null,
            vendor_id: isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            expense_date: $data['expense_date'],
            description: $data['description'],
            amount: (float) $data['amount'],
            cashback: (float) ($data['cashback'] ?? 0.0),
            notes: $data['notes'] ?? null,
            is_recurring: in_array($frequency, ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'], true),
            frequency: $frequency,
            nature: $data['nature'] ?? null,
            transaction_kind: $data['transaction_kind'] ?? 'expense',
            file_path: isset($data['file_path']) ? (string) $data['file_path'] : null,
            created_by: $data['created_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->category_id,
            'expense_for_id' => $this->expense_for_id,
            'payment_method_id' => $this->payment_method_id,
            'account_id' => $this->account_id,
            'payment_platform_id' => $this->payment_platform_id,
            'vendor_id' => $this->vendor_id,
            'expense_date' => $this->expense_date,
            'description' => $this->description,
            'amount' => $this->amount,
            'cashback' => $this->cashback,
            'notes' => $this->notes,
            'is_recurring' => $this->is_recurring,
            'frequency' => $this->frequency,
            'nature' => $this->nature,
            'transaction_kind' => $this->transaction_kind,
            'file_path' => $this->file_path,
            'created_by' => $this->created_by,
        ];
    }
}
