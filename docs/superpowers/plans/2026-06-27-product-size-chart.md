# Product Size Chart Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a product optionally carry a per-size measurements table ("size guide") that admins manage and both the web and mobile apps display.

**Architecture:** Store the guide as a nullable JSON column `size_chart` on `products`, cast to an array on the `Product` model so the existing product API returns it automatically. The admin Blade product form gains a small editor; the React web product page and Flutter user product screen render a table when the field is present and nothing when it is null.

**Tech Stack:** Laravel 11 (modular, nwidart), MySQL, Blade + Alpine/vanilla JS, React + Redux Toolkit (Vite), Flutter (bloc/cubit).

## Global Constraints

- Unit is always `"cm"`; set it server-side, never trust the client.
- `size_chart` is `null` when absent; every display surface must render nothing when null.
- Canonical JSON shape:
  `{ "unit": "cm", "columns": ["Shoulder", ...], "rows": [ { "size": "S", "values": { "Shoulder": 42, ... } } ] }`
- Additive and backward-compatible: existing products read back `null`.
- Measurement values are numeric (decimals allowed), `>= 0`; blank value renders as `—`.
- Follow existing patterns in each codebase; do not restructure unrelated code.

---

### Task 1: Backend — `size_chart` column, model cast, API exposure

**Files:**
- Create: `narzinapp-main/Modules/ProductManagement/database/migrations/2026_06_27_000000_add_size_chart_to_products_table.php`
- Modify: `narzinapp-main/Modules/ProductManagement/app/Models/Product.php:19-35`
- Test: `narzinapp-main/tests/Feature/ProductSizeChartTest.php`

**Interfaces:**
- Produces: `products.size_chart` (nullable JSON) and `Product->size_chart` (array|null). The product API `GET /api/v1/products/{id}` returns `data.size_chart` (array or null) with no controller change, because `show` already returns the whole `$product`.

- [ ] **Step 1: Write the failing test**

Create `narzinapp-main/tests/Feature/ProductSizeChartTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProductManagement\Models\Product;
use Tests\TestCase;

class ProductSizeChartTest extends TestCase
{
    use RefreshDatabase;

    private array $chart = [
        'unit' => 'cm',
        'columns' => ['Shoulder', 'Chest'],
        'rows' => [
            ['size' => 'S', 'values' => ['Shoulder' => 42, 'Chest' => 96]],
            ['size' => 'M', 'values' => ['Shoulder' => 44, 'Chest' => 100]],
        ],
    ];

    public function test_product_persists_and_casts_size_chart(): void
    {
        $product = Product::create([
            'name_arabic' => 'قميص',
            'name_german' => 'Hemd',
            'slug_arabic' => 'qamis-' . uniqid(),
            'slug_german' => 'hemd-' . uniqid(),
            'category_id' => 1,
            'is_active' => true,
            'size_chart' => $this->chart,
        ]);

        $fresh = Product::find($product->id);
        $this->assertIsArray($fresh->size_chart);
        $this->assertSame('cm', $fresh->size_chart['unit']);
        $this->assertSame(42, $fresh->size_chart['rows'][0]['values']['Shoulder']);
    }

    public function test_size_chart_defaults_to_null(): void
    {
        $product = Product::create([
            'name_arabic' => 'حذاء',
            'name_german' => 'Schuh',
            'slug_arabic' => 'hidaa-' . uniqid(),
            'slug_german' => 'schuh-' . uniqid(),
            'category_id' => 1,
            'is_active' => true,
        ]);

        $this->assertNull(Product::find($product->id)->size_chart);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=ProductSizeChartTest`
Expected: FAIL — `size_chart` column/attribute does not exist (SQL error or null).

- [ ] **Step 3: Write the migration**

Create the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('size_chart')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('size_chart');
        });
    }
};
```

- [ ] **Step 4: Add fillable + cast to the model**

In `Product.php`, add `'size_chart'` to `$fillable` (after `'weight',`) and the cast:

```php
    protected $casts = [
        'is_active' => 'boolean',
        'size_chart' => 'array',
    ];
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `cd narzinapp-main && php artisan test --filter=ProductSizeChartTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add narzinapp-main/Modules/ProductManagement/database/migrations/2026_06_27_000000_add_size_chart_to_products_table.php \
        narzinapp-main/Modules/ProductManagement/app/Models/Product.php \
        narzinapp-main/tests/Feature/ProductSizeChartTest.php
git commit -m "feat(products): add nullable size_chart column + array cast"
```

---

### Task 2: Backend — admin controller validation + save

**Files:**
- Modify: `narzinapp-main/Modules/Admin/app/Http/Controllers/ProductController.php` (the `store` and `update` methods)
- Test: `narzinapp-main/tests/Feature/ProductSizeChartTest.php` (add cases)

**Interfaces:**
- Consumes: `Product->size_chart` array cast from Task 1.
- Produces: a private helper `normalizeSizeChart(?array $input): ?array` on the admin `ProductController` that returns a clean chart (with `unit` forced to `cm`) or `null`. Both `store` and `update` call it and assign the result to the product's `size_chart`.

- [ ] **Step 1: Write the failing test**

Add to `ProductSizeChartTest.php`:

```php
    public function test_normalize_forces_cm_and_nulls_empty(): void
    {
        $c = new \Modules\Admin\Http\Controllers\ProductController();
        $m = new \ReflectionMethod($c, 'normalizeSizeChart');
        $m->setAccessible(true);

        // empty -> null
        $this->assertNull($m->invoke($c, null));
        $this->assertNull($m->invoke($c, ['columns' => [], 'rows' => []]));

        // valid -> unit forced to cm
        $out = $m->invoke($c, [
            'unit' => 'inches',
            'columns' => ['Shoulder'],
            'rows' => [['size' => 'S', 'values' => ['Shoulder' => '42.5']]],
        ]);
        $this->assertSame('cm', $out['unit']);
        $this->assertSame(['Shoulder'], $out['columns']);
        $this->assertSame(42.5, $out['rows'][0]['values']['Shoulder']);
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd narzinapp-main && php artisan test --filter=test_normalize_forces_cm_and_nulls_empty`
Expected: FAIL — `normalizeSizeChart` method does not exist.

- [ ] **Step 3: Add the validation rules and helper, and call it in store/update**

In `Modules/Admin/app/Http/Controllers/ProductController.php`, add these rules to the `$request->validate([...])` (or Validator) array in BOTH `store` and `update`:

```php
            'size_chart' => 'nullable|array',
            'size_chart.columns' => 'nullable|array',
            'size_chart.columns.*' => 'required|string|max:50',
            'size_chart.rows' => 'nullable|array',
            'size_chart.rows.*.size' => 'required|string|max:50',
            'size_chart.rows.*.values' => 'nullable|array',
            'size_chart.rows.*.values.*' => 'nullable|numeric|min:0',
```

Add the helper method to the controller class:

```php
    private function normalizeSizeChart(?array $input): ?array
    {
        if (!$input) {
            return null;
        }
        $columns = array_values(array_unique(array_filter(
            array_map('trim', $input['columns'] ?? [])
        )));
        $rows = [];
        foreach ($input['rows'] ?? [] as $row) {
            $size = trim($row['size'] ?? '');
            if ($size === '') {
                continue;
            }
            $values = [];
            foreach ($columns as $col) {
                $v = $row['values'][$col] ?? null;
                $values[$col] = ($v === null || $v === '') ? null : (float) $v;
            }
            $rows[] = ['size' => $size, 'values' => $values];
        }
        if (empty($columns) || empty($rows)) {
            return null;
        }
        return ['unit' => 'cm', 'columns' => $columns, 'rows' => $rows];
    }
```

In `store` and `update`, after the product is created/fetched and other fields are
set, assign:

```php
        $product->size_chart = $this->normalizeSizeChart($request->input('size_chart'));
        $product->save();
```

(Place it where the product instance is available and saved — match the existing
save flow in each method.)

- [ ] **Step 4: Run tests to verify they pass**

Run: `cd narzinapp-main && php artisan test --filter=ProductSizeChartTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add narzinapp-main/Modules/Admin/app/Http/Controllers/ProductController.php \
        narzinapp-main/tests/Feature/ProductSizeChartTest.php
git commit -m "feat(admin): validate and normalize product size_chart on save"
```

---

### Task 3: Admin Blade — size guide editor

**Files:**
- Create: `narzinapp-main/Modules/Admin/resources/views/products/partials/size-chart-editor.blade.php`
- Modify: `narzinapp-main/Modules/Admin/resources/views/products/create.blade.php`
- Modify: `narzinapp-main/Modules/Admin/resources/views/products/edit.blade.php`

**Interfaces:**
- Consumes: the validation field names from Task 2 (`size_chart[columns][]`,
  `size_chart[rows][i][size]`, `size_chart[rows][i][values][Label]`).
- Produces: form fields that POST a `size_chart` structure the controller parses.

- [ ] **Step 1: Create the editor partial (Alpine-based)**

Create `partials/size-chart-editor.blade.php`. It accepts an optional
`$sizeChart` variable (array|null) for prefill:

```blade
@php($sizeChart = $sizeChart ?? null)
<div class="card p-3 mb-3" x-data='sizeChartEditor(@json($sizeChart))'>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Size guide (optional, cm)</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" @click="addColumn()">+ Measurement</button>
    </div>

    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th style="min-width:90px">Size</th>
                <template x-for="(col, ci) in columns" :key="ci">
                    <th>
                        <input class="form-control form-control-sm" x-model="columns[ci]"
                               :name="`size_chart[columns][]`" placeholder="e.g. Shoulder">
                        <button type="button" class="btn btn-sm text-danger p-0" @click="removeColumn(ci)">remove</button>
                    </th>
                </template>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, ri) in rows" :key="ri">
                <tr>
                    <td>
                        <input class="form-control form-control-sm" x-model="row.size"
                               :name="`size_chart[rows][${ri}][size]`" placeholder="e.g. M">
                    </td>
                    <template x-for="(col, ci) in columns" :key="ci">
                        <td>
                            <input type="number" step="0.1" min="0" class="form-control form-control-sm"
                                   x-model="row.values[col]"
                                   :name="`size_chart[rows][${ri}][values][${col}]`">
                        </td>
                    </template>
                    <td><button type="button" class="btn btn-sm text-danger" @click="removeRow(ri)">×</button></td>
                </tr>
            </template>
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-secondary" @click="addRow()">+ Size row</button>
</div>

<script>
function sizeChartEditor(initial) {
    return {
        columns: (initial && initial.columns) ? [...initial.columns] : [],
        rows: (initial && initial.rows)
            ? initial.rows.map(r => ({ size: r.size, values: { ...r.values } }))
            : [],
        addColumn() { this.columns.push(''); },
        removeColumn(ci) {
            const name = this.columns[ci];
            this.columns.splice(ci, 1);
            this.rows.forEach(r => delete r.values[name]);
        },
        addRow() { this.rows.push({ size: '', values: {} }); },
        removeRow(ri) { this.rows.splice(ri, 1); },
    };
}
</script>
```

(If the admin theme does not already load Alpine.js, add
`<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>`
to the admin layout's `<head>` — check `Modules/Admin/resources/views/layouts`
first and reuse if present.)

- [ ] **Step 2: Include the partial in the create form**

In `create.blade.php`, inside the product `<form>` (before the submit button),
add:

```blade
        @include('admin::products.partials.size-chart-editor')
```

- [ ] **Step 3: Include the partial in the edit form with prefill**

In `edit.blade.php`, inside the `<form>`, add (passing the existing value):

```blade
        @include('admin::products.partials.size-chart-editor', ['sizeChart' => $product->size_chart])
```

- [ ] **Step 4: Manually verify in the browser**

Run the admin panel, create a product, add 2 measurement columns + 2 size rows
with cm values, save. Edit the product and confirm the table prefilled. Save with
all rows/columns removed and confirm the product's `size_chart` becomes null
(`php artisan tinker` → `Product::find($id)->size_chart`).
Expected: present when filled, `null` when emptied.

- [ ] **Step 5: Commit**

```bash
git add narzinapp-main/Modules/Admin/resources/views/products/
git commit -m "feat(admin): size guide editor on product create/edit forms"
```

---

### Task 4: Web — display size guide on the product page

**Files:**
- Create: `narzin-main/src/components/pages/singleProduct/SizeGuide.jsx`
- Modify: `narzin-main/src/pages/ProductPage.jsx`

**Interfaces:**
- Consumes: `product.size_chart` (array or null) from the product API response,
  via the existing single-product Redux state (`SingleProductSlice.js`).
- Produces: `<SizeGuide sizeChart={...} />` — renders a table or `null`.

- [ ] **Step 1: Create the SizeGuide component**

Create `narzin-main/src/components/pages/singleProduct/SizeGuide.jsx`:

```jsx
export default function SizeGuide({ sizeChart }) {
  if (!sizeChart || !sizeChart.columns?.length || !sizeChart.rows?.length) {
    return null;
  }
  const { columns, rows, unit = "cm" } = sizeChart;
  return (
    <section className="my-6">
      <h3 className="text-lg font-semibold mb-2">Size guide ({unit})</h3>
      <div className="overflow-x-auto">
        <table className="min-w-full text-sm border">
          <thead>
            <tr className="bg-gray-50">
              <th className="border px-3 py-2 text-left">Size</th>
              {columns.map((c) => (
                <th key={c} className="border px-3 py-2 text-left">{c}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {rows.map((r, i) => (
              <tr key={i}>
                <td className="border px-3 py-2 font-medium">{r.size}</td>
                {columns.map((c) => (
                  <td key={c} className="border px-3 py-2">
                    {r.values?.[c] ?? "—"}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}
```

(Match the project's actual styling convention — the classes above assume Tailwind,
which this project uses. Adjust to neighboring components if they differ.)

- [ ] **Step 2: Render it on the product page**

In `narzin-main/src/pages/ProductPage.jsx`, import and place the component where
the product details render (near the description / `FullDescription`):

```jsx
import SizeGuide from "../components/pages/singleProduct/SizeGuide";
// ... inside the JSX, where `product` (single product object) is in scope:
<SizeGuide sizeChart={product?.size_chart} />
```

(Use whatever variable holds the fetched single product in this file — confirm by
reading the existing render; it comes from the SingleProduct Redux slice.)

- [ ] **Step 3: Build to verify it compiles**

Run: `cd narzin-main && npm run build`
Expected: build succeeds (no unresolved import / syntax errors).

- [ ] **Step 4: Manually verify**

Run `npm run dev`, open a product that has a size guide → table shows; open one
without → nothing renders, no console errors.

- [ ] **Step 5: Commit**

```bash
git add narzin-main/src/components/pages/singleProduct/SizeGuide.jsx narzin-main/src/pages/ProductPage.jsx
git commit -m "feat(web): show product size guide on product page"
```

---

### Task 5: Mobile — parse and display size guide

**Files:**
- Modify: `Narzin-app/user/narzin/lib/model_layer/single_product_model.dart`
- Create: `Narzin-app/user/narzin/lib/widgets/app_infrastructure_widgets/size_guide_widget.dart`
- Modify: `Narzin-app/user/narzin/lib/presentation_layer/main_app_user/products_screens/product_details_screen.dart`

**Interfaces:**
- Consumes: `size_chart` JSON in the single-product API response.
- Produces: `SizeChart?` model field on the single product, and a
  `SizeGuideWidget(sizeChart: ...)` that renders a table or `SizedBox.shrink()`.

- [ ] **Step 1: Add a SizeChart model and field**

In `single_product_model.dart`, add a `SizeChart` class and a nullable
`sizeChart` field on the product model, parsed in `fromJson`:

```dart
class SizeChart {
  final String unit;
  final List<String> columns;
  final List<SizeRow> rows;

  SizeChart({required this.unit, required this.columns, required this.rows});

  factory SizeChart.fromJson(Map<String, dynamic> json) => SizeChart(
        unit: json['unit'] ?? 'cm',
        columns: List<String>.from(json['columns'] ?? const []),
        rows: (json['rows'] as List? ?? const [])
            .map((r) => SizeRow.fromJson(Map<String, dynamic>.from(r)))
            .toList(),
      );
}

class SizeRow {
  final String size;
  final Map<String, dynamic> values;
  SizeRow({required this.size, required this.values});
  factory SizeRow.fromJson(Map<String, dynamic> json) => SizeRow(
        size: json['size'] ?? '',
        values: Map<String, dynamic>.from(json['values'] ?? const {}),
      );
}
```

In the product class's `fromJson`, add:

```dart
    sizeChart = json['size_chart'] == null
        ? null
        : SizeChart.fromJson(Map<String, dynamic>.from(json['size_chart']));
```

and declare the field: `SizeChart? sizeChart;`

- [ ] **Step 2: Create the size guide widget**

Create `size_guide_widget.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:narzin/model_layer/single_product_model.dart';

class SizeGuideWidget extends StatelessWidget {
  final SizeChart? sizeChart;
  const SizeGuideWidget({super.key, required this.sizeChart});

  @override
  Widget build(BuildContext context) {
    final sc = sizeChart;
    if (sc == null || sc.columns.isEmpty || sc.rows.isEmpty) {
      return const SizedBox.shrink();
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Text('Size guide (${sc.unit})',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
        ),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: DataTable(
            columns: [
              const DataColumn(label: Text('Size')),
              ...sc.columns.map((c) => DataColumn(label: Text(c))),
            ],
            rows: sc.rows.map((r) {
              return DataRow(cells: [
                DataCell(Text(r.size)),
                ...sc.columns.map((c) =>
                    DataCell(Text(r.values[c]?.toString() ?? '—'))),
              ]);
            }).toList(),
          ),
        ),
      ],
    );
  }
}
```

- [ ] **Step 3: Render it on the product details screen**

In `product_details_screen.dart`, import the widget and place it in the details
column (near the description), passing the product's `sizeChart`:

```dart
import 'package:narzin/widgets/app_infrastructure_widgets/size_guide_widget.dart';
// ... where the product detail object is in scope:
SizeGuideWidget(sizeChart: product.sizeChart),
```

(Use the actual variable name holding the single product in this screen.)

- [ ] **Step 4: Analyze to verify it compiles**

Run: `cd Narzin-app/user/narzin && flutter analyze lib/widgets/app_infrastructure_widgets/size_guide_widget.dart lib/model_layer/single_product_model.dart lib/presentation_layer/main_app_user/products_screens/product_details_screen.dart`
Expected: no `error`-level issues.

- [ ] **Step 5: Commit**

```bash
git add Narzin-app/user/narzin/lib/model_layer/single_product_model.dart \
        Narzin-app/user/narzin/lib/widgets/app_infrastructure_widgets/size_guide_widget.dart \
        Narzin-app/user/narzin/lib/presentation_layer/main_app_user/products_screens/product_details_screen.dart
git commit -m "feat(mobile): show product size guide on product details screen"
```

---

## Deployment

After merge to `main`: the API auto-deploy runs the migration (`deploy-api.sh`)
and the web auto-deploy rebuilds and ships the React change. The mobile change
ships in the next APK/AAB build. No manual DB steps.

## Self-review notes

- Spec coverage: data model (Task 1), admin save/validation (Task 2), admin editor
  (Task 3), API exposure (Task 1, automatic via model), web display (Task 4),
  mobile display (Task 5), optional/null handling (every task), cm-only enforced
  (Task 2). Testing covered for backend (PHPUnit) and compile/render checks for
  the frontends (these codebases have no JS unit-test harness; mobile uses
  `flutter analyze`).
- All field names are consistent across tasks: `size_chart` /
  `size_chart[columns][]` / `size_chart[rows][i][size]` /
  `size_chart[rows][i][values][Label]`; model field `size_chart` (PHP/JSON),
  `sizeChart` (Dart).
