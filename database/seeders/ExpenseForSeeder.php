<?php

namespace Database\Seeders;

use App\Models\ExpenseFor;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseForSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id') ?? 1;

        $items = [
            ['name' => 'Personal', 'color_code' => '#0d6efd', 'sort_order' => 1],
            ['name' => 'Family', 'color_code' => '#198754', 'sort_order' => 2],
            ['name' => 'Office', 'color_code' => '#fd7e14', 'sort_order' => 3],
            ['name' => 'Business', 'color_code' => '#6f42c1', 'sort_order' => 4],
        ];

        foreach ($items as $item) {
            ExpenseFor::updateOrCreate(
                ['name' => $item['name']],
                $item + [
                    'is_active' => true,
                    'created_by' => $userId,
                ]
            );
        }
    }
}