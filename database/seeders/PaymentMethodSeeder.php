<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $methods = [
            ['name' => 'Cash', 'code' => 'CASH'],
            ['name' => 'Debit Card', 'code' => 'DC'],
            ['name' => 'Credit Card', 'code' => 'CC'],
            ['name' => 'UPI', 'code' => 'UPI'],
            ['name' => 'Bank Transfer', 'code' => 'BT'],
            ['name' => 'Cheque', 'code' => 'CHQ'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create([
                'name' => $method['name'],
                'code' => $method['code'],
                'created_by' => $user->id,
            ]);
        }
    }
}
