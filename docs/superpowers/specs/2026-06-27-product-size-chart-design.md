# Product Size Chart (Measurements) — Design

**Date:** 2026-06-27
**Status:** Approved (design)

## Summary

Let a product optionally carry a **size guide**: a free-form table of body/garment
measurements in centimetres, with one row per size and product-defined columns
(e.g. Shoulder, Chest, Length). Admins manage it in the Blade admin panel. The
product API returns it, and both the web (React) and mobile (Flutter user) apps
display it as a "Size guide" table on the product page. When a product has no
size guide, nothing is shown.

## Goals

- Per-product, **optional** measurements table.
- **Per-size** rows (matches how real garments differ across sizes).
- **Free-form** columns chosen per product (shirts vs pants need different measures).
- Unit is centimetres (cm).
- Display on web product page and mobile product screen.
- Managed in the admin panel (v1).

## Non-goals (v1)

- Editing the size guide from the vendor Flutter app / vendor API (phase 2).
- Linking measurements to specific purchasable variants (it is a per-product
  reference table, not a variant attribute).
- Unit switching (cm only for now).
- Auto-recommending a size to the customer.

## Data model

Add a single nullable JSON column `size_chart` to the existing `products` table.

Canonical shape (stored as JSON, cast to array in the model):

```json
{
  "unit": "cm",
  "columns": ["Shoulder", "Chest", "Length"],
  "rows": [
    { "size": "S", "values": { "Shoulder": 42, "Chest": 96, "Length": 68 } },
    { "size": "M", "values": { "Shoulder": 44, "Chest": 100, "Length": 70 } },
    { "size": "L", "values": { "Shoulder": 46, "Chest": 104, "Length": 72 } }
  ]
}
```

Rules:
- `size_chart` is `null` when the product has no guide.
- `unit` is always `"cm"` in v1.
- `columns` is a non-empty list of distinct measurement labels (strings).
- `rows` is a non-empty list; each row has a `size` label (string) and a `values`
  map keyed by the column labels. A missing/blank value renders as "—".
- Values are numbers (allow decimals, e.g. 70.5). Non-numeric is rejected on save.

Rationale for JSON over normalized tables: the data is optional, small, read as a
unit, and the columns vary per product — a JSON column avoids three extra tables
and join logic. Rejected: normalized `product_size_charts`/rows/cells (overkill);
reusing `variant_attributes`/`variant_values` (measurements are not variants).

## Backend (`narzinapp-main`)

1. **Migration** — `Modules/ProductManagement/database/migrations/*_add_size_chart_to_products_table.php`:
   `$table->json('size_chart')->nullable();` (appended to `products`; column
   position is cosmetic, so no `after()` anchor to avoid coupling to other columns).
2. **Model** — `Modules/ProductManagement/app/Models/Product.php`:
   add `'size_chart'` to `$fillable`; cast `'size_chart' => 'array'`.
3. **Admin controller** — `Modules/Admin/app/Http/Controllers/ProductController.php`
   (`store` + `update`): validate and persist `size_chart`. Normalize empty input
   (no columns or no rows) to `null`. Validation (nullable):
   - `size_chart.columns` → `array`, `min:1`, each `string|max:50`, distinct.
   - `size_chart.rows` → `array`, `min:1`.
   - `size_chart.rows.*.size` → `string|max:50`.
   - `size_chart.rows.*.values.*` → `nullable|numeric|min:0`.
   Force `unit = "cm"` server-side (don't trust client).
4. **API** — `Modules/ProductManagement/app/Http/Controllers/V1/Api/ProductController.php`:
   include `size_chart` in `show` (and `index` if product cards need it; `show` is
   the primary need). Returns the array or `null`.

### API contract

`GET /api/v1/products/{id}` response gains a `size_chart` field on the product
object: either `null` or the canonical object above. No new endpoints.

## Admin panel (Blade)

On the product **create** and **edit** forms
(`Modules/Admin/resources/views/products/{create,edit}.blade.php`), add a
collapsible "Size guide (optional)" section:

- "Add column" adds a measurement label input (header).
- "Add size" adds a row: a size-label input plus one cm input per column.
- "Remove" buttons for columns and rows.
- Submitting with zero columns or zero rows sends nothing → stored as `null`.
- On edit, prefill from the existing `size_chart`.

Implemented with a small vanilla-JS/Alpine widget that serializes the table into
`size_chart[columns][]`, `size_chart[rows][i][size]`,
`size_chart[rows][i][values][Label]` form fields, matching the validation above.
(Follow whatever JS approach the existing product form already uses.)

## Web (`narzin-main`, React)

On the product detail page, if `product.size_chart` is present, render a
"Size guide" table: a header row of `Size` + each column label, then one row per
`rows[]` entry, cells from `values[column]` (blank → "—"), with a "(cm)" note.
Hidden entirely when `size_chart` is null. Reuses existing product-page styling.

## Mobile (Flutter user app, `Narzin-app/user`)

- Extend the product model to parse `size_chart` (nullable).
- On the product screen, if present, show a "Size guide" section (e.g. an
  expandable panel) rendering the same table. Hidden when null. Localized section
  title (Arabic/German per existing localization).

## Edge cases

- No size guide → field is `null`, no UI shown anywhere.
- A row missing a value for some column → cell shows "—".
- Long tables → horizontal scroll on small screens (web + mobile).
- Admin submits malformed/partial data → validation rejects with field errors;
  empty table normalizes to `null` rather than erroring.

## Testing

- **Backend (PHPUnit):** migration runs; product saves/loads a `size_chart`;
  validation rejects non-numeric values and empty columns; empty input stores
  `null`; API `show` returns the field (object and null cases).
- **Web:** product page renders the table when present and renders nothing when
  null.
- **Mobile:** product model parses present/null; size-guide widget shows/hides.

## Rollout

Additive and backward-compatible: the column is nullable, all existing products
read back `null`, and every display surface no-ops when it's null. Ships via the
normal deploy (migration runs in `deploy-api.sh`).
