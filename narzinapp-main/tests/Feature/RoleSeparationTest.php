<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\Vendor\Models\Vendor;
use Tests\TestCase;

class RoleSeparationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'U', 'email' => 'u' . uniqid() . '@t.test',
            'password' => 'secret123', 'email_verified_at' => now(),
        ]);
    }

    private function makeVendor(int $userId): Vendor
    {
        return Vendor::create([
            'user_id' => $userId, 'store_name_in_arabic' => 'م',
            'store_name_in_german' => 'L', 'status' => 'Active',
        ]);
    }

    public function test_an_admin_cannot_also_become_a_vendor(): void
    {
        $u = $this->user();
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 1]);

        $this->expectException(\DomainException::class);
        $this->makeVendor($u->id);
    }

    public function test_a_vendor_cannot_be_granted_admin(): void
    {
        $u = $this->user();
        $this->makeVendor($u->id);

        $this->expectException(\DomainException::class);
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 1]);
    }

    public function test_separate_users_can_each_hold_a_single_role(): void
    {
        $vendorUser = $this->user();
        $adminUser = $this->user();

        $vendor = $this->makeVendor($vendorUser->id);
        $admin = UserAdmin::create(['user_id' => $adminUser->id, 'is_active' => 1]);

        $this->assertNotNull($vendor->id);
        $this->assertNotNull($admin->id);
    }
}
