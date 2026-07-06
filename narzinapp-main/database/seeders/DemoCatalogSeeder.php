<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductImage;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\ProductManagement\Models\VariantAttribute;
use Modules\ProductManagement\Models\VariantValue;

/**
 * Seeds a comprehensive bilingual (Arabic / German) e-commerce category tree
 * plus demo products (with images + in-stock variants) so the storefront and
 * homepage auto-feed have real content to show.
 *
 * Design notes:
 *  - Idempotent: categories are firstOrCreate'd on their German slug; products
 *    are skipped if one with the same German name already exists in the category.
 *  - Images: a small pool (~12) is downloaded once to the public disk and reused
 *    across products/categories, so we make ~12 HTTP calls, not hundreds.
 *  - Products have no vendor (vendor_id is nullable); the price rail falls back
 *    to the global platform markup, which is exactly what the resolver expects.
 */
class DemoCatalogSeeder extends Seeder
{
    /** parent [arabic, german] => children [[arabic, german], ...] (German names kept unique for slugs) */
    private array $taxonomy = [
        ['إلكترونيات', 'Elektronik', [
            ['هواتف ذكية', 'Smartphones'], ['أجهزة لوحية', 'Tablets'], ['حواسيب محمولة', 'Laptops'],
            ['سماعات', 'Kopfhörer'], ['كاميرات', 'Kameras'], ['ملحقات إلكترونية', 'Elektronikzubehör'],
        ]],
        ['أزياء نسائية', 'Damenmode', [
            ['فساتين', 'Kleider'], ['بلوزات', 'Blusen'], ['تنانير', 'Röcke'],
            ['أحذية نسائية', 'Damenschuhe'], ['حقائب نسائية', 'Damentaschen'],
        ]],
        ['أزياء رجالية', 'Herrenmode', [
            ['قمصان', 'Hemden'], ['بناطيل', 'Herrenhosen'], ['جاكيتات', 'Jacken'],
            ['أحذية رجالية', 'Herrenschuhe'], ['ملابس داخلية رجالية', 'Herrenunterwäsche'],
        ]],
        ['أطفال ومواليد', 'Kinder & Baby', [
            ['ملابس أطفال', 'Kinderkleidung'], ['ألعاب أطفال', 'Kinderspielzeug'],
            ['عربات أطفال', 'Kinderwagen'], ['مستلزمات الرضاعة', 'Babypflege'],
        ]],
        ['المنزل والمطبخ', 'Haus & Küche', [
            ['أثاث', 'Möbel'], ['أدوات مطبخ', 'Küchenutensilien'], ['ديكور منزلي', 'Dekoration'],
            ['أدوات المائدة', 'Geschirr'], ['إضاءة', 'Beleuchtung'],
        ]],
        ['الجمال والعناية', 'Beauty & Pflege', [
            ['مكياج', 'Make-up'], ['عطور', 'Parfüm'], ['العناية بالبشرة', 'Hautpflege'],
            ['العناية بالشعر', 'Haarpflege'],
        ]],
        ['الرياضة واللياقة', 'Sport & Fitness', [
            ['ملابس رياضية', 'Sportbekleidung'], ['معدات رياضية', 'Sportgeräte'],
            ['دراجات', 'Fahrräder'], ['مكملات غذائية', 'Nahrungsergänzung'],
        ]],
        ['الصحة', 'Gesundheit', [
            ['أجهزة طبية', 'Medizinprodukte'], ['فيتامينات', 'Vitamine'], ['إسعافات أولية', 'Erste Hilfe'],
        ]],
        ['الكتب والقرطاسية', 'Bücher & Schreibwaren', [
            ['كتب', 'Bücher'], ['قرطاسية', 'Schreibwaren'], ['مستلزمات فنية', 'Kunstbedarf'],
        ]],
        ['الألعاب والهوايات', 'Spielzeug & Hobby', [
            ['ألعاب لوحية', 'Brettspiele'], ['ألعاب فيديو', 'Videospiele'], ['نماذج ومجسمات', 'Modellbau'],
        ]],
        ['السيارات', 'Automobil', [
            ['قطع غيار', 'Autoteile'], ['إكسسوارات السيارات', 'Autozubehör'], ['زيوت وسوائل', 'Öle & Flüssigkeiten'],
        ]],
        ['البقالة', 'Lebensmittel', [
            ['مشروبات', 'Getränke'], ['وجبات خفيفة', 'Snacks'], ['توابل', 'Gewürze'], ['معلبات', 'Konserven'],
        ]],
    ];

    public function run(): void
    {
        $pool = $this->seedImagePool();
        $this->command?->info('Image pool ready: ' . count($pool) . ' images.');

        $catCount = 0;
        $prodCount = 0;

        foreach ($this->taxonomy as [$parentAr, $parentDe, $children]) {
            $parent = $this->makeCategory($parentAr, $parentDe, null, $pool);
            $catCount++;

            foreach ($children as [$childAr, $childDe]) {
                $child = $this->makeCategory($childAr, $childDe, $parent->id, $pool);
                $catCount++;
                $prodCount += $this->makeProducts($parent, $child, $pool);
            }
        }

        $this->seedImageColors();
        $this->seedVariantSizes();

        $this->command?->info("Done. Categories ensured: {$catCount}, products created: {$prodCount}.");
    }

    /**
     * Tag each product's images with a color (the storefront's texture-based
     * color system: a colored image IS the swatch). Gives each product a couple
     * of distinct colors so the color selector + filter have data. Idempotent —
     * skips images that already carry a color, so it never overwrites real data.
     */
    private function seedImageColors(): void
    {
        $palette = [
            "#1F2937", "#C5A880", "#D4AF37", "#7C3AED", "#DC2626",
            "#059669", "#2563EB", "#DB2777", "#F59E0B", "#0EA5E9",
        ];

        Product::select("id")->orderBy("id")->chunk(100, function ($products) use ($palette) {
            foreach ($products as $p) {
                $imgs = DB::table("products_images")
                    ->where("product_id", $p->id)
                    ->orderBy("id")
                    ->get();
                foreach ($imgs as $i => $img) {
                    if (empty($img->color)) {
                        DB::table("products_images")
                            ->where("id", $img->id)
                            ->update(["color" => $palette[($p->id + $i) % count($palette)]]);
                    }
                }
            }
        });
    }

    /**
     * Give every variant a size (variant_values against the size attribute), so
     * the size filter populates. Idempotent — skips variants that already have a
     * size value.
     */
    private function seedVariantSizes(): void
    {
        $sizeAttr = VariantAttribute::firstOrCreate(
            ["name_arabic" => "المقاس"],
            ["name_german" => "Größe"]
        );
        $sizes = ["S", "M", "L", "XL"];

        ProductVariant::select("id")->orderBy("id")->chunk(200, function ($variants) use ($sizeAttr, $sizes) {
            foreach ($variants as $v) {
                $exists = VariantValue::where("product_variants_id", $v->id)
                    ->where("variant_attribute_id", $sizeAttr->id)
                    ->exists();
                if (! $exists) {
                    VariantValue::create([
                        "product_variants_id" => $v->id,
                        "variant_attribute_id" => $sizeAttr->id,
                        "value" => $sizes[$v->id % count($sizes)],
                    ]);
                }
            }
        });
    }

    /** Download a reusable pool of images to the public disk once. Returns relative paths. */
    private function seedImagePool(): array
    {
        $disk = Storage::disk('public');
        $paths = [];

        for ($i = 1; $i <= 12; $i++) {
            $rel = "demo/img{$i}.jpg";
            if (! $disk->exists($rel)) {
                $bin = @file_get_contents("https://picsum.photos/seed/narzin{$i}/700/700");
                if ($bin === false || $bin === '') {
                    continue;
                }
                $disk->put($rel, $bin);
            }
            $paths[] = $rel;
        }

        return $paths;
    }

    private function makeCategory(string $ar, string $de, ?int $parentId, array $pool): Category
    {
        return Category::firstOrCreate(
            ['slug_german' => Str::slug($de)],
            [
                'name_arabic' => $ar,
                'name_german' => $de,
                'slug_arabic' => $this->arabicSlug($ar),
                'image'       => $pool ? $pool[array_rand($pool)] : null,
                'parent_id'   => $parentId,
            ]
        );
    }

    /** Create up to 2 products for a leaf category. Returns how many were created. */
    private function makeProducts(Category $parent, Category $child, array $pool): int
    {
        $created = 0;

        for ($i = 1; $i <= 2; $i++) {
            $nameDe = "{$child->name_german} Modell {$i}";
            $nameAr = "{$child->name_arabic} موديل {$i}";

            if (Product::where('name_german', $nameDe)->where('category_id', $parent->id)->exists()) {
                continue;
            }

            $product = Product::create([
                'name_arabic'         => $nameAr,
                'name_german'         => $nameDe,
                'slug_arabic'         => $this->arabicSlug($nameAr) . '-' . Str::lower(Str::random(4)),
                'slug_german'         => Str::slug($nameDe) . '-' . Str::lower(Str::random(4)),
                'description_arabic'  => "منتج تجريبي: {$nameAr}. جودة عالية وسعر مناسب.",
                'description_german'  => "Demo-Produkt: {$nameDe}. Hohe Qualität zu einem fairen Preis.",
                'category_id'         => $parent->id,
                'child_category_id'   => $child->id,
                'vendor_id'           => null,
                'is_active'           => true,
            ]);

            foreach (collect($pool)->shuffle()->take(min(2, count($pool))) as $img) {
                ProductImage::create(['product_id' => $product->id, 'image' => $img]);
            }

            for ($v = 1; $v <= 2; $v++) {
                ProductVariant::create([
                    'product_id'      => $product->id,
                    'price'           => rand(15, 480) + 0.99,
                    'stock'           => rand(5, 60),
                    'sku'             => "NRZ-{$product->id}-{$v}",
                    'is_active'       => true,
                    'is_out_of_stock' => false,
                ]);
            }

            $created++;
        }

        return $created;
    }

    /** Slug that preserves Arabic letters (Str::slug would strip them). */
    private function arabicSlug(string $text): string
    {
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/u', '-', $text);
        $text = trim($text, '-');

        return $text !== '' ? $text : Str::lower(Str::random(8));
    }
}
