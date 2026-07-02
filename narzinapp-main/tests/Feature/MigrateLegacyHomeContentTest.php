<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class MigrateLegacyHomeContentTest extends TestCase
{
    use RefreshDatabase;

    private function seedLegacy(): void
    {
        DB::table('before_nav')->insert([
            'text' => 'شحن مجاني فوق ٥٠ يورو',
            'start_date' => now()->subDay(), 'end_date' => now()->addMonth(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('banners')->insert([
            ['image' => 'bannersImages/web1.jpg', 'title' => 'صيف', 'description' => 'تخفيضات', 'is_mobile' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['image' => 'bannersImages/app1.jpg', 'title' => null, 'description' => null, 'is_mobile' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_converts_legacy_rows_to_blocks(): void
    {
        $this->seedLegacy();

        $this->artisan('home:migrate-legacy')->assertExitCode(0);

        $announcement = HomeBlock::where('type', 'announcement_bar')->firstOrFail();
        $this->assertSame('شحن مجاني فوق ٥٠ يورو', $announcement->content['text']['ar']);
        $this->assertTrue($announcement->is_active);

        $hero = HomeBlock::where('type', 'hero_slider')->firstOrFail();
        $this->assertSame('Legacy hero slider', $hero->name);
        $this->assertCount(2, $hero->content['slides']);
        $this->assertSame('bannersImages/web1.jpg', $hero->content['slides'][0]['image_web']);
        $this->assertSame('bannersImages/app1.jpg', $hero->content['slides'][1]['image_app']);
    }

    public function test_command_is_idempotent(): void
    {
        $this->seedLegacy();

        $this->artisan('home:migrate-legacy')->assertExitCode(0);
        $this->artisan('home:migrate-legacy')->assertExitCode(0);

        $this->assertSame(2, HomeBlock::count());
    }

    public function test_no_legacy_data_is_a_noop(): void
    {
        $this->artisan('home:migrate-legacy')->assertExitCode(0);
        $this->assertSame(0, HomeBlock::count());
    }
}
