<?php 

namespace Modules\Vendor\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Vendor\Models\Vendor;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $storeTypes = ['Retail', 'Wholesale', 'Online', 'Franchise'];
        $users = User::whereBetween('id', [14, 23])->get();
        
        $arabicStoreNames = [
            'متجر الأناقة',
            'السوق الذهبي',
            'متجر النخبة',
            'دار الموضة',
            'متجر الجودة',
            'سوق العائلة',
            'متجر المستقبل',
            'دار الأزياء',
            'متجر الفخامة',
            'سوق النجوم'
        ];
        
        $germanStoreNames = [
            'Eleganz Laden',
            'Der Goldene Markt',
            'Elite Shop',
            'Modehaus',
            'Qualität Store',
            'Familienmarkt',
            'Zukunft Shop',
            'Modeboutique',
            'Luxus Laden',
            'Sternemarkt'
        ];

        foreach ($users as $index => $user) {
            Vendor::create([
                'user_id' => $user->id,
                'store_name_in_arabic' => $arabicStoreNames[$index],
                'store_name_in_german' => $germanStoreNames[$index],
                'store_logo' => 'vendors/store-' . ($index + 1) . '.jpg',
                'address' => 'Street ' . ($index + 1) . ', City',
                'phone' => '+1234567890' . $index,
                'store_type' => $storeTypes[array_rand($storeTypes)],
                'store_id' => 'STR' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'latitude' => (string)(48.1351 + (rand(-1000, 1000) / 10000)), // Random coordinates around Germany
                'longitude' => (string)(11.5820 + (rand(-1000, 1000) / 10000)),
                'status' => ['Waiting Approve', 'Active', 'Rejected'][rand(0, 2)]
            ]);
        }
    }
}