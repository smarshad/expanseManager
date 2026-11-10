<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Services\ExpenseClassificationService;
use PHPUnit\Framework\TestCase;

class ExpenseClassificationServiceTest extends TestCase
{
    private function category(array $attrs, ?Category $parent = null): Category
    {
        $category = new Category($attrs);
        $category->setRelation('parent', $parent);

        return $category;
    }

    public function test_unmapped_category_uses_defaults(): void
    {
        $this->assertSame(
            ['transaction_kind' => 'expense', 'spending_nature' => 'unclassified', 'recurrence' => 'none'],
            (new ExpenseClassificationService())->classify($this->category([]))
        );
    }

    public function test_child_inherits_from_parent_and_can_override(): void
    {
        $parent = $this->category(['spending_nature' => 'essential', 'recurrence' => 'monthly']);
        $inherits = $this->category([], $parent);
        $overrides = $this->category(['spending_nature' => 'discretionary'], $parent);
        $service = new ExpenseClassificationService();

        $this->assertSame('essential', $service->classify($inherits)['spending_nature']);
        $this->assertSame('monthly', $service->classify($inherits)['recurrence']);
        $this->assertSame('discretionary', $service->classify($overrides)['spending_nature']);
    }

    public function test_non_expense_kind_is_never_given_a_spending_nature(): void
    {
        $category = $this->category(['transaction_kind' => 'investment', 'spending_nature' => 'essential']);

        $this->assertSame(
            ['transaction_kind' => 'investment', 'spending_nature' => 'unclassified', 'recurrence' => 'none'],
            (new ExpenseClassificationService())->classify($category)
        );
    }
}
