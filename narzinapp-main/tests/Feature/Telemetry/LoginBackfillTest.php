<?php

namespace Tests\Feature\Telemetry;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Telemetry\Services\CaptureService;
use Tests\TestCase;

class LoginBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_backfills_user_id_onto_session(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        CaptureService::recordSession('sess-guest', null, ['utm_source' => 'google']);

        $this->postJson('/api/v1/login', [
            'email' => 'buyer@test.com',
            'password' => 'password123',
            'session_id' => 'sess-guest',
        ])->assertStatus(200);

        $this->assertDatabaseHas('visit_sessions', [
            'session_id' => 'sess-guest', 'user_id' => $user->id, 'utm_source' => 'google',
        ]);
    }
}
