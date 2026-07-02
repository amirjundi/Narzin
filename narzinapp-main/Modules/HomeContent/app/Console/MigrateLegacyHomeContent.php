<?php

namespace Modules\HomeContent\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HomeContent\Models\HomeBlock;

class MigrateLegacyHomeContent extends Command
{
    protected $signature = 'home:migrate-legacy';

    protected $description = 'Convert legacy before_nav and banners rows into home_blocks';

    public function handle(): int
    {
        if (HomeBlock::where('name', 'like', 'Legacy%')->exists()) {
            $this->info('Legacy content already migrated; nothing to do.');

            return self::SUCCESS;
        }

        $created = DB::transaction(function () {
            $sort = (int) HomeBlock::max('sort_order');
            $created = 0;

            foreach (DB::table('before_nav')->orderBy('created_at')->get() as $row) {
                HomeBlock::create([
                    'type' => 'announcement_bar',
                    'name' => 'Legacy announcement #' . $row->id,
                    'platform' => 'both',
                    'is_active' => true,
                    'starts_at' => $row->start_date,
                    'ends_at' => $row->end_date !== null ? Carbon::parse($row->end_date)->endOfDay() : null,
                    'sort_order' => ++$sort,
                    'content' => [
                        'text' => ['ar' => $row->text],
                        'bg_color' => '#141923',
                        'text_color' => '#C5A880',
                    ],
                ]);
                $created++;
            }

            $banners = DB::table('banners')->orderBy('created_at')->get();

            $webSlides = $banners->where('is_mobile', 0)->map(fn ($banner) => [
                'image_web' => $banner->image,
                'image_app' => null,
                'title' => $banner->title ? ['ar' => $banner->title] : null,
                'subtitle' => $banner->description ? ['ar' => $banner->description] : null,
                'link' => null,
            ])->values()->all();

            if (!empty($webSlides)) {
                HomeBlock::create([
                    'type' => 'hero_slider',
                    'name' => 'Legacy hero slider (web)',
                    'platform' => 'web',
                    'is_active' => true,
                    'sort_order' => ++$sort,
                    'content' => ['slides' => $webSlides],
                ]);
                $created++;
            }

            $appSlides = $banners->where('is_mobile', 1)->map(fn ($banner) => [
                'image_web' => null,
                'image_app' => $banner->image,
                'title' => $banner->title ? ['ar' => $banner->title] : null,
                'subtitle' => $banner->description ? ['ar' => $banner->description] : null,
                'link' => null,
            ])->values()->all();

            if (!empty($appSlides)) {
                HomeBlock::create([
                    'type' => 'hero_slider',
                    'name' => 'Legacy hero slider (app)',
                    'platform' => 'app',
                    'is_active' => true,
                    'sort_order' => ++$sort,
                    'content' => ['slides' => $appSlides],
                ]);
                $created++;
            }

            return $created;
        });

        $this->info("Created {$created} home blocks from legacy data.");

        return self::SUCCESS;
    }
}
