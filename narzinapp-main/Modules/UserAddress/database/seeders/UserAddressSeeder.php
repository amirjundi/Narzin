<?php 


namespace Modules\UserAddress\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\UserAddress\Models\UserAddress;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            // Create 2 addresses for each user
            for ($i = 1; $i <= 2; $i++) {
            UserAddress::create([
                'user_id' => $user->id,
                'country_id' => rand(1, 2), 
                'address' => "Address {$i}",
                'city_id' => rand(1, 4), 
                'postal_code' => rand(10000, 99999),
                'latitude' =>  rand(1, 8),
                'longitude' =>  rand(1, 8),
            ]);
            }
        }
    }
}
