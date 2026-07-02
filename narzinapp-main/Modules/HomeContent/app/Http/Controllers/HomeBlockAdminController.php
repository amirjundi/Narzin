<?php

namespace Modules\HomeContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Modules\HomeContent\Models\HomeBlock;
use Modules\HomeContent\Services\HomeFeedService;
use Modules\HomeContent\Services\ProductRailResolver;
use Modules\HomeContent\Support\BlockContentRules;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;

class HomeBlockAdminController extends Controller
{
    public function index()
    {
        $blocks = HomeBlock::orderBy('sort_order')->get();

        return view('homecontent::index', compact('blocks'));
    }

    public function create(Request $request)
    {
        $type = (string) $request->query('type');
        abort_unless(in_array($type, HomeBlock::TYPES, true), 404);

        $block = new HomeBlock(['type' => $type, 'platform' => 'both', 'is_active' => true, 'content' => []]);

        return view('homecontent::form', compact('block', 'type'));
    }

    public function store(Request $request)
    {
        $type = (string) $request->input('type');
        abort_unless(in_array($type, HomeBlock::TYPES, true), 422);

        $data = $this->validated($request, $type);
        $content = $this->mergeUploadedImages($request, $type, $data['content'] ?? []);
        $this->assertImagesPresent($type, $content);

        HomeBlock::create([
            'type' => $type,
            'name' => $data['name'],
            'platform' => $data['platform'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'sort_order' => (int) HomeBlock::max('sort_order') + 1,
            'content' => $content,
        ]);

        return redirect()->route('home-blocks.index')->with('success', 'Block created successfully');
    }

    public function edit($id)
    {
        $block = HomeBlock::findOrFail($id);
        $type = $block->type;

        return view('homecontent::form', compact('block', 'type'));
    }

    public function update(Request $request, $id)
    {
        $block = HomeBlock::findOrFail($id);
        $data = $this->validated($request, $block->type);
        $content = $this->mergeUploadedImages($request, $block->type, $data['content'] ?? []);
        $this->assertImagesPresent($block->type, $content);

        $block->update([
            'name' => $data['name'],
            'platform' => $data['platform'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'content' => $content,
        ]);

        return redirect()->route('home-blocks.index')->with('success', 'Block updated successfully');
    }

    public function destroy($id)
    {
        HomeBlock::findOrFail($id)->delete();

        return redirect()->route('home-blocks.index')->with('success', 'Block deleted successfully');
    }

    public function reorder(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        foreach (array_values($ids) as $position => $id) {
            HomeBlock::where('id', $id)->update(['sort_order' => $position]);
        }
        HomeFeedService::flushCache(); // query-builder updates bypass model events

        return response()->json(['status' => true]);
    }

    public function toggle($id)
    {
        $block = HomeBlock::findOrFail($id);
        $block->is_active = !$block->is_active;
        $block->save();

        return response()->json(['status' => true, 'is_active' => $block->is_active]);
    }

    public function searchProducts(Request $request)
    {
        $q = (string) $request->query('q', '');

        $products = Product::where('is_active', true)
            ->where(fn ($w) => $w
                ->where('name_arabic', 'like', "%{$q}%")
                ->orWhere('name_german', 'like', "%{$q}%"))
            ->limit(10)
            ->get(['id', 'name_arabic', 'name_german']);

        return response()->json(['status' => true, 'data' => $products]);
    }

    public function searchCategories(Request $request)
    {
        $q = (string) $request->query('q', '');

        // Category's default global scope does CONCAT(app.url, image) in SQL,
        // which MySQL supports but sqlite (used in tests) does not. This endpoint
        // never returns the image column, so drop the scope entirely (same
        // workaround already applied in ProductRailResolver / HomeFeedService).
        $categories = Category::withoutGlobalScope('image_url')
            ->where(fn ($w) => $w
                ->where('name_arabic', 'like', "%{$q}%")
                ->orWhere('name_german', 'like', "%{$q}%"))
            ->limit(10)
            ->get(['id', 'name_arabic', 'name_german']);

        return response()->json(['status' => true, 'data' => $categories]);
    }

    public function railPreview(Request $request, ProductRailResolver $resolver)
    {
        $content = $request->validate([
            'rule' => ['required', 'in:newest,best_sellers,category,manual'],
            'category_id' => ['nullable', 'integer'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer'],
        ]);
        $content['limit'] = 6;

        return response()->json(['status' => true, 'data' => $resolver->resolve($content)]);
    }

    private function validated(Request $request, string $type): array
    {
        return $request->validate(array_merge(
            [
                'name' => ['required', 'string', 'max:100'],
                'platform' => ['required', 'in:web,app,both'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
                'is_active' => ['nullable', 'boolean'],
            ],
            BlockContentRules::for($type),
            BlockContentRules::files($type),
        ));
    }

    private function mergeUploadedImages(Request $request, string $type, array $content): array
    {
        $store = fn (UploadedFile $file) => $file->store('homeBlocks', 'public');

        if ($type === 'popup' && $request->hasFile('popup_image')) {
            $content['image'] = $store($request->file('popup_image'));
        }
        if ($type === 'countdown_banner' && $request->hasFile('countdown_image')) {
            $content['image'] = $store($request->file('countdown_image'));
        }
        if ($type === 'hero_slider') {
            $indices = array_keys(array_replace(
                $content['slides'] ?? [],
                $request->file('slide_images_web') ?? [],
                $request->file('slide_images_app') ?? [],
            ));
            foreach ($indices as $i) {
                if ($request->hasFile("slide_images_web.{$i}")) {
                    $content['slides'][$i]['image_web'] = $store($request->file("slide_images_web.{$i}"));
                }
                if ($request->hasFile("slide_images_app.{$i}")) {
                    $content['slides'][$i]['image_app'] = $store($request->file("slide_images_app.{$i}"));
                }
            }
            if (isset($content['slides'])) {
                ksort($content['slides']);
                $content['slides'] = array_values($content['slides']);
            }
        }
        if ($type === 'promo_tiles') {
            $indices = array_keys(array_replace(
                $content['tiles'] ?? [],
                $request->file('tile_images') ?? [],
            ));
            foreach ($indices as $i) {
                if ($request->hasFile("tile_images.{$i}")) {
                    $content['tiles'][$i]['image'] = $store($request->file("tile_images.{$i}"));
                }
            }
            if (isset($content['tiles'])) {
                ksort($content['tiles']);
                $content['tiles'] = array_values($content['tiles']);
            }
        }

        return $content;
    }

    private function assertImagesPresent(string $type, array $content): void
    {
        if ($type === 'hero_slider') {
            foreach ($content['slides'] ?? [] as $i => $slide) {
                if (empty($slide['image_web']) && empty($slide['image_app'])) {
                    throw ValidationException::withMessages([
                        'content.slides' => 'Slide ' . ($i + 1) . ' needs a web or app image.',
                    ]);
                }
            }
        }
        if ($type === 'promo_tiles') {
            foreach ($content['tiles'] ?? [] as $i => $tile) {
                if (empty($tile['image'])) {
                    throw ValidationException::withMessages([
                        'content.tiles' => 'Tile ' . ($i + 1) . ' needs an image.',
                    ]);
                }
            }
        }
    }
}
