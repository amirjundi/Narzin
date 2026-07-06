<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApiRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_customer_user_type_reference_row_exists(): void
    {
        // RegisterController assigns user_type_id=1; this reference row must
        // exist on every environment (guaranteed by the reference-data migration).
        $this->assertDatabaseHas('user_types', ['id' => 1, 'name' => 'Customer']);
    }

    public function test_a_customer_can_register_via_the_api(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/register', [
            'name' => 'New Customer',
            'email' => 'newcustomer@example.com',
            'password' => 'Password#2026',
            'password_confirmation' => 'Password#2026',
        ]);

        $response->assertStatus(201)->assertJsonPath('status', true);

        // The whole point of the fix: the FK to user_types(1) is satisfied.
        $this->assertDatabaseHas('users', [
            'email' => 'newcustomer@example.com',
            'user_type_id' => 1,
        ]);
    }
}
