<?php

namespace Tests\Unit;

use App\DTOs\ExpenseDTO;
use PHPUnit\Framework\TestCase;

class ExpenseDTOTest extends TestCase
{
    public function test_monthly_frequency_is_recurring(): void
    {
        $dto = ExpenseDTO::fromArray($this->data(['frequency' => 'monthly']));

        $this->assertTrue($dto->is_recurring);
        $this->assertSame('monthly', $dto->toArray()['frequency']);
    }

    public function test_occasional_frequency_is_not_recurring_but_is_preserved(): void
    {
        $dto = ExpenseDTO::fromArray($this->data(['frequency' => 'occasionally']));

        $this->assertFalse($dto->is_recurring);
        $this->assertSame('occasionally', $dto->toArray()['frequency']);
    }

    public function test_transaction_kind_is_preserved(): void
    {
        $dto = ExpenseDTO::fromArray($this->data(['transaction_kind' => 'transfer']));

        $this->assertSame('transfer', $dto->toArray()['transaction_kind']);
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'category_id' => 1,
            'expense_for_id' => 1,
            'payment_method_id' => 1,
            'account_id' => 1,
            'expense_date' => '2026-01-01',
            'description' => 'Test',
            'amount' => 100,
        ], $overrides);
    }
}
