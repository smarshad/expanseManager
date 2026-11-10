<?php

namespace App\Services;

use App\Models\Category;

/**
 * Resolves transaction_kind / spending_nature / recurrence for a category from
 * the mapping stored on the categories table (editable from the UI). A NULL
 * value on a category is inherited from its parent, then from the defaults.
 */
class ExpenseClassificationService
{
    public const KINDS = ['expense', 'investment', 'transfer', 'debt'];
    public const NATURES = ['essential', 'discretionary', 'unclassified'];
    public const RECURRENCES = ['none', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'];

    /**
     * @return array{transaction_kind: string, spending_nature: string, recurrence: string}
     */
    public function classify(?Category $category): array
    {
        $kind = $nature = $recurrence = null;

        for ($node = $category, $depth = 0; $node && $depth < 5; $node = $node->parent, $depth++) {
            $kind ??= $node->transaction_kind;
            $nature ??= $node->spending_nature;
            $recurrence ??= $node->recurrence;
        }

        $kind ??= 'expense';

        return [
            'transaction_kind' => $kind,
            'spending_nature' => $kind === 'expense' ? ($nature ?? 'unclassified') : 'unclassified',
            'recurrence' => $recurrence ?? 'none',
        ];
    }
}
