<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 20;

        $vendors = [
            ['DMart', 'Shop'],
            ['Reliance Smart', 'Shop'],
            ['Local Grocery Shop', 'Shop'],
            ['Vegetable Vendor', 'Shop'],
            ['Fruit Vendor', 'Shop'],
            ['Amul', 'Shop'],
        
            ['Swiggy', 'Restaurant'],
            ['Zomato', 'Restaurant'],
            ['Restaurant', 'Restaurant'],
            ['Tea Stall', 'Restaurant'],
        
            ['Indian Oil', 'Fuel'],
            ['HP Petrol Pump', 'Fuel'],
            ['Bharat Petroleum', 'Fuel'],
            ['Bike Garage', 'Fuel'],
        
            ['Uber', 'Transport'],
            ['Rapido', 'Transport'],
            ['Auto Rickshaw', 'Transport'],
            ['Indian Railways', 'Transport'],
        
            ['MSEDCL', 'Utility'],
            ['Jio', 'Utility'],
            ['Airtel', 'Utility'],
            ['Vi', 'Utility'],
        
            ['Amazon', 'Online'],
            ['Flipkart', 'Online'],
            ['Myntra', 'Online'],
        
            ['Apollo Pharmacy', 'Medical'],
            ['Medical Store', 'Medical'],
            ['Hospital', 'Medical'],
            ['Doctor', 'Medical'],
        
            ['Groww', 'Bank'],
            ['Zerodha', 'Bank'],
            ['HDFC Bank', 'Bank'],
            ['ICICI Bank', 'Bank'],
            ['SBI', 'Bank'],
        
            ['Maid', 'Person'],
            ['Family', 'Person'],
            ['Salma', 'Person'],
            ['Other', 'Other'],
        ];

        foreach ($vendors as [$name, $type]) {
            Vendor::updateOrCreate(
                [
                    'user_id' => $userId,
                    'name' => $name,
                ],
                [
                    'vendor_type' => $type,
                    'is_active' => true,
                ]
            );
        }
    }
}