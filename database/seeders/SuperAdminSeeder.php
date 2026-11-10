<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the user who will be super admin
        $superAdminRole = Role::find(3);
        // Example: user with ID 1
        $user = User::find(1);

        if ($user) {
            // Assign the role
            $user->assignRole($superAdminRole);
        }
    }
}
