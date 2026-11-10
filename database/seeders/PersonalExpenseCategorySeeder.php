<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonalExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id') ?? 1;

        $tree = [
            'Household' => ['Maid Salary', 'Wife Pocket Money', 'Home Expenses', 'Home Repair', 'Gas Cylinder', 'Cleaning Supplies'],
            'Groceries' => ['Vegetables', 'Fruits', 'Milk', 'Bakery', 'Rice', 'Flour', 'Oil', 'Spices', 'General Grocery'],
            'Vehicle' => ['Petrol', 'Service', 'Repair', 'Insurance', 'Parking', 'Toll'],
            'Travel' => ['Auto', 'Bus', 'Train', 'Metro', 'Flight', 'Uber', 'Rapido'],
            'Food & Dining' => ['Restaurant', 'Tea', 'Coffee', 'Snacks', 'Juice', 'Ice Cream'],
            'Medical' => ['Medicines', 'Doctor Consultation', 'Hospital', 'Lab Tests', 'Dental', 'Eye Care'],
            'Utilities' => ['Electricity', 'Water', 'Internet', 'Mobile Recharge', 'DTH'],
            'Family' => ['Parents', 'Children', 'Gifts'],
            'Shopping' => ['Clothes', 'Shoes', 'Electronics', 'Furniture'],
            'Personal Care' => ['Haircut', 'Salon', 'Gym', 'Cosmetics'],
            'Entertainment' => ['Movie', 'OTT', 'Games', 'Outing'],
            'Investment' => ['SIP', 'Mutual Fund', 'Stocks', 'Gold', 'FD'],
            'Insurance' => ['Health Insurance', 'Life Insurance', 'Vehicle Insurance'],
            'Education' => ['Books', 'Courses', 'Fees'],
            'Donation' => ['Zakat', 'Charity', 'Mosque'],
            'Income' => ['Salary', 'Freelancing', 'Interest', 'Cashback'],
            'Miscellaneous' => ['Other'],
        ];

        $parentSort = 1;
        foreach ($tree as $parentName => $children) {
            $parent = Category::firstOrCreate(
                ['name' => $parentName, 'parent_id' => null],
                [
                    'created_by' => $userId,
                    'is_active' => true,
                    'sort_order' => $parentSort++,
                    'budget_type' => in_array($parentName, ['Medical', 'Vehicle']) ? 'no_budget' : 'soft_budget',
                    'nature' => $parentName === 'Investment' ? 'investment' : ($parentName === 'Entertainment' ? 'want' : 'need'),
                ]
            );

            foreach ($children as $index => $childName) {
                Category::firstOrCreate(
                    ['name' => $childName, 'parent_id' => $parent->id],
                    [
                        'created_by' => $userId,
                        'is_active' => true,
                        'sort_order' => $index + 1,
                        'budget_type' => $parent->budget_type,
                        'nature' => $parent->nature,
                    ]
                );
            }
        }
    }
}
