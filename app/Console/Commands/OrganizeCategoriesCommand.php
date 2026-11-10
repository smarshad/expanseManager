<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrganizeCategoriesCommand extends Command
{
    protected $signature = 'categories:organize
                            {--apply : Create parents and attach matching categories}
                            {--user= : Limit category owners to one user ID}';

    protected $description = 'Preview or apply the high-confidence two-level category taxonomy';

    private const TAXONOMY = [
        'Household' => [
            'gas',
            'gas cylinder',
            'home items',
            'maid',
            'rent',
            'salma/maid/home',
        ],
        'Groceries' => [
            'atta',
            'chicken',
            'fruits',
            'grocery',
            'milk',
            'mutton',
            'vegetables',
        ],
        'Vehicle' => [
            'petrol',
        ],
        'Travel' => [
            'travelling',
        ],
        'Food & Dining' => [
            'breakfast',
            'dinner',
            'food',
            'lunch',
            'snacks',
            'tea',
        ],
        'Medical' => [
            'alshifa',
            'medical group',
            'medicine',
        ],
        'Utilities' => [
            'light bill',
            'mobile recharge',
            'recharge',
            'water',
        ],
        'Personal Care' => [
            'saloon',
        ],
        'Investment' => [
            'ipo',
            'mutual funds',
            'stocks',
        ],
        'Education' => [
            'educate child',
            'fees',
            'madarsa',
            'school fees',
            'stationary',
        ],
        'Finance & Transfers' => [
            'credit card bill',
            'return borrow',
            'withdrawal',
        ],
    ];

    public function handle(): int
    {
        $query = Category::query()
            ->whereNull('parent_id')
            ->orderBy('created_by')
            ->orderBy('name');

        if ($this->option('user')) {
            $query->where('created_by', (int) $this->option('user'));
        }

        $categories = $query->get();
        $plan = $this->buildPlan($categories);

        $this->table(
            ['Parent', 'Existing categories', 'Owners', 'Rows'],
            $plan->map(fn (array $item): array => [
                $item['parent'],
                $item['categories']->pluck('name')->unique()->sort()->implode(', '),
                $item['categories']->pluck('created_by')->unique()->sort()->implode(', '),
                $item['categories']->count(),
            ])->values()->all()
        );

        $count = $plan->sum(fn (array $item): int => $item['categories']->count());
        if (!$this->option('apply')) {
            $this->comment("Dry run only. Would attach {$count} category row(s).");
            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan): void {
            foreach ($plan as $item) {
                $item['categories']
                    ->groupBy('created_by')
                    ->each(function (Collection $children, int|string $ownerId) use ($item): void {
                        $parent = Category::query()
                            ->where('created_by', (int) $ownerId)
                            ->whereNull('parent_id')
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower($item['parent'])])
                            ->first();

                        if (!$parent) {
                            $parent = Category::create([
                                'name' => $item['parent'],
                                'parent_id' => null,
                                'description' => 'Parent category',
                                'color_code' => null,
                                'is_active' => true,
                                'created_by' => (int) $ownerId,
                            ]);
                        }

                        Category::query()
                            ->whereKey($children->pluck('id'))
                            ->whereKeyNot($parent->id)
                            ->whereNull('parent_id')
                            ->update(['parent_id' => $parent->id]);
                    });
            }
        });

        $this->info("Attached {$count} category row(s).");

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, array{parent: string, categories: Collection<int, Category>}>
     */
    private function buildPlan(Collection $categories): Collection
    {
        return collect(self::TAXONOMY)
            ->map(function (array $children, string $parent) use ($categories): array {
                return [
                    'parent' => $parent,
                    'categories' => $categories
                        ->filter(fn (Category $category): bool =>
                            in_array(mb_strtolower(trim($category->name)), $children, true)
                            && mb_strtolower(trim($category->name)) !== mb_strtolower($parent)
                        )
                        ->values(),
                ];
            })
            ->filter(fn (array $item): bool => $item['categories']->isNotEmpty())
            ->values();
    }
}
