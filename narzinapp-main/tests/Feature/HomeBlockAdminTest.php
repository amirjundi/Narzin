<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Admin\Models\UserAdmin;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeBlockAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create([
            'name' => 'A', 'email' => 'a' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);

        return $user;
    }

    private function block(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['text' => ['ar' => 'أهلا']],
        ], $overrides));
    }

    public function test_guests_cannot_reach_the_builder(): void
    {
        $this->get(route('home-blocks.index'))->assertRedirect();
    }

    public function test_admin_can_create_an_announcement_bar(): void
    {
        $this->actingAs($this->admin())
            ->post(route('home-blocks.store'), [
                'type' => 'announcement_bar', 'name' => 'App promo', 'platform' => 'web',
                'is_active' => 1,
                'content' => ['text' => ['en' => 'Download our app'], 'bg_color' => '#141923'],
            ])
            ->assertRedirect(route('home-blocks.index'));

        $block = HomeBlock::firstOrFail();
        $this->assertSame('announcement_bar', $block->type);
        $this->assertSame('Download our app', $block->content['text']['en']);
    }

    public function test_validation_rejects_empty_translations(): void
    {
        $this->actingAs($this->admin())
            ->from(route('home-blocks.create', ['type' => 'announcement_bar']))
            ->post(route('home-blocks.store'), [
                'type' => 'announcement_bar', 'name' => 'Bad', 'platform' => 'web',
                'content' => ['text' => ['ar' => '', 'de' => '', 'en' => '']],
            ])
            ->assertSessionHasErrors();

        $this->assertSame(0, HomeBlock::count());
    }

    public function test_admin_can_create_popup_with_uploaded_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('home-blocks.store'), [
                'type' => 'popup', 'name' => 'Get the app', 'platform' => 'web', 'is_active' => 1,
                'content' => [
                    'title' => ['en' => 'Get the app'],
                    'frequency' => ['mode' => 'once_per_days', 'days' => 7],
                    'delay_seconds' => 3,
                ],
                'popup_image' => UploadedFile::fake()->image('popup.jpg', 600, 800),
            ])
            ->assertRedirect(route('home-blocks.index'));

        $block = HomeBlock::firstOrFail();
        $this->assertStringStartsWith('homeBlocks/', $block->content['image']);
        Storage::disk('public')->assertExists($block->content['image']);
    }

    public function test_hero_slide_without_any_image_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('home-blocks.create', ['type' => 'hero_slider']))
            ->post(route('home-blocks.store'), [
                'type' => 'hero_slider', 'name' => 'Hero', 'platform' => 'both',
                'content' => ['slides' => [['title' => ['ar' => 'صيف']]]],
            ])
            ->assertSessionHasErrors();
    }

    public function test_hero_slide_with_only_uploaded_image_is_kept(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('home-blocks.store'), [
                'type' => 'hero_slider', 'name' => 'Hero', 'platform' => 'both',
                'content' => ['slides' => [['title' => ['ar' => 'صيف']]]],
                'slide_images_web' => [
                    0 => UploadedFile::fake()->image('slide0.jpg', 1200, 600),
                    1 => UploadedFile::fake()->image('slide1.jpg', 1200, 600),
                ],
            ])
            ->assertRedirect(route('home-blocks.index'));

        $block = HomeBlock::firstOrFail();
        $slides = $block->content['slides'];
        $this->assertCount(2, $slides);
        $this->assertNotEmpty($slides[0]['image_web'] ?? null);
        $this->assertNotEmpty($slides[1]['image_web'] ?? null);
        Storage::disk('public')->assertExists($slides[0]['image_web']);
        Storage::disk('public')->assertExists($slides[1]['image_web']);
    }

    public function test_admin_can_update_and_delete(): void
    {
        $block = $this->block();

        $this->actingAs($this->admin())
            ->put(route('home-blocks.update', $block), [
                'name' => 'Renamed', 'platform' => 'app', 'is_active' => 0,
                'content' => ['text' => ['de' => 'Hallo']],
            ])
            ->assertRedirect(route('home-blocks.index'));
        $this->assertSame('Renamed', $block->fresh()->name);

        $this->actingAs($this->admin())->delete(route('home-blocks.destroy', $block));
        $this->assertSame(0, HomeBlock::count());
    }

    public function test_reorder_rewrites_sort_order(): void
    {
        $first = $this->block(['name' => 'first', 'sort_order' => 0]);
        $second = $this->block(['name' => 'second', 'sort_order' => 1]);

        $this->actingAs($this->admin())
            ->postJson(route('home-blocks.reorder'), ['ids' => [$second->id, $first->id]])
            ->assertOk();

        $this->assertSame(0, $second->fresh()->sort_order);
        $this->assertSame(1, $first->fresh()->sort_order);
    }

    public function test_toggle_flips_active_flag(): void
    {
        $block = $this->block(['is_active' => true]);

        $this->actingAs($this->admin())
            ->postJson(route('home-blocks.toggle', $block))
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->assertFalse($block->fresh()->is_active);
    }

    public function test_search_endpoints_return_matches(): void
    {
        $category = \Modules\ProductManagement\Models\Category::create([
            'name_arabic' => 'فساتين', 'name_german' => 'Kleider',
            'slug_arabic' => 'k-ar-' . uniqid(), 'slug_german' => 'k-de-' . uniqid(),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('home-blocks.search.categories', ['q' => 'Klei']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $category->id);
    }
}
