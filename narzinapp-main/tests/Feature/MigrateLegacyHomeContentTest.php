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

        $this->assertSame(3, HomeBlock::count());

        $announcement = HomeBlock::where('type', 'announcement_bar')->firstOrFail();
        $this->assertSame('شحن مجاني فوق ٥٠ يورو', $announcement->content['text']['ar']);
        $this->assertTrue($announcement->is_active);
        $this->assertSame(
            now()->addMonth()->endOfDay()->format('Y-m-d H:i:s'),
            $announcement->ends_at->format('Y-m-d H:i:s')
        );

        $webHero = HomeBlock::where('name', 'Legacy hero slider (web)')->firstOrFail();
        $this->assertSame('hero_slider', $webHero->type);
        $this->assertSame('web', $webHero->platform);
        $this->assertCount(1, $webHero->content['slides']);
        $this->assertSame('bannersImages/web1.jpg', $webHero->content['slides'][0]['image_web']);
        $this->assertNull($webHero->content['slides'][0]['image_app']);

        $appHero = HomeBlock::where('name', 'Legacy hero slider (app)')->firstOrFail();
        $this->assertSame('hero_slider', $appHero->type);
        $this->assertSame('app', $appHero->platform);
        $this->assertCount(1, $appHero->content['slides']);
        $this->assertSame('bannersImages/app1.jpg', $appHero->content['slides'][0]['image_app']);
        $this->assertNull($appHero->content['slides'][0]['image_web']);
    }

    public function test_command_is_idempotent(): void
    {
        $this->seedLegacy();

        $this->artisan('home:migrate-legacy')->assertExitCode(0);
        $this->artisan('home:migrate-legacy')->assertExitCode(0);

        $this->assertSame(3, HomeBlock::count());
    }

    public function test_no_legacy_data_is_a_noop(): void
    {
        $this->artisan('home:migrate-legacy')->assertExitCode(0);
        $this->assertSame(0, HomeBlock::count());
    }
}
