<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\Vendor\Models\Vendor;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@narzin.test'],
            [
                'name' => 'Narzin Admin',
                'password' => Hash::make('password123'),
                'user_type_id' => 3, // Admin
                'email_verified_at' => now(),
            ]
        );

        // Add admin role entry
        \Modules\Admin\Models\UserAdmin::firstOrCreate(
            ['user_id' => $admin->id],
            ['is_active' => 1]
        );

        // 2. Create Vendor User
        $vendorUser = User::firstOrCreate(
            ['email' => 'vendor@narzin.test'],
            [
                'name' => 'Test Vendor',
                'password' => Hash::make('password123'),
                'user_type_id' => 2, // Vendor
                'email_verified_at' => now(),
            ]
        );

        // Create Vendor Profile for the Vendor User
        Vendor::firstOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'store_name_in_arabic' => 'متجر تجريبي',
                'store_name_in_german' => 'Testgeschäft',
                'address' => 'Baghdad, Iraq',
                'phone' => '07700000000',
                'store_type' => 'Electronics',
                'status' => 'Active', // Assuming 'status' is used for vendor approval
            ]
        );

        // 3. Create Customer User
        $customer = User::firstOrCreate(
            ['email' => 'customer@narzin.test'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('password123'),
                'user_type_id' => 1, // Customer
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Test users created successfully!');
        $this->command->info('--------------------------------');
        $this->command->info('Admin: admin@narzin.test / password123');
        $this->command->info('Vendor: vendor@narzin.test / password123');
        $this->command->info('Customer: customer@narzin.test / password123');
    }
}
