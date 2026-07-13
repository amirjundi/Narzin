<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Support\DateRange;

/** Read-only inventory analytics over product_variants (+ products/order_items). */
class InventoryService
{
    private const CAP = 200;

    public function valuation(): array
    {
        // Single aggregate over active variants of non-deleted products.
        // Join products + whereNull(deleted_at) so soft-deleted catalog items
        // don't inflate valuation, keeping totals equal to the breakdowns below.
        $totals = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->where('pv.is_active', 1)
            ->whereNull('p.deleted_at')
            ->selectRaw('COALESCE(SUM(pv.stock),0) as units, COALESCE(SUM(pv.stock*pv.cost),0) as cost_val, COALESCE(SUM(pv.stock*pv.price),0) as retail_val')
            ->first();

        $byCategory = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->where('pv.is_active', 1)
            ->whereNull('p.deleted_at')
            ->groupBy('c.id', 'c.name_german', 'c.name_arabic')
            ->selectRaw("COALESCE(c.name_german, c.name_arabic, '(none)') as name, SUM(pv.stock) as units, SUM(pv.stock*pv.cost) as cost_val, SUM(pv.stock*pv.price) as retail_val")
            ->orderByDesc('cost_val')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'units' => (int) $r->units, 'value_at_cost' => round((float) $r->cost_val, 2), 'value_at_retail' => round((float) $r->retail_val, 2)]);

        $byVendor = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->whereNull('p.deleted_at')
            ->groupBy('v.id', 'v.store_name_in_german')
            ->selectRaw("COALESCE(v.store_name_in_german, '(none)') as name, SUM(pv.stock) as units, SUM(pv.stock*pv.cost) as cost_val, SUM(pv.stock*pv.price) as retail_val")
            ->orderByDesc('cost_val')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'units' => (int) $r->units, 'value_at_cost' => round((float) $r->cost_val, 2), 'value_at_retail' => round((float) $r->retail_val, 2)]);

        $cost = round((float) $totals->cost_val, 2);
        $retail = round((float) $totals->retail_val, 2);

        return [
            'total_units' => (int) $totals->units,
            'value_at_cost' => $cost,
            'value_at_retail' => $retail,
            'potential_margin' => round($retail - $cost, 2),
            'by_category' => $byCategory,
            'by_vendor' => $byVendor,
        ];
    }

    public function reorderWorklist(): Collection
    {
        $threshold = (int) config('telemetry.low_stock_threshold', 5);

        return DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('pv.stock', '<=', $threshold)
            ->orderBy('pv.stock')
            ->orderBy('p.name_german')
            ->limit(self::CAP)
            ->get(['pv.sku', 'pv.stock', 'p.name_arabic', 'p.name_german', DB::raw("COALESCE(v.store_name_in_german, '(none)') as vendor_name")])
            ->map(fn ($r) => [
                'sku' => $r->sku,
                'product_name_arabic' => $r->name_arabic,
                'product_name_german' => $r->name_german,
                'stock' => (int) $r->stock,
                'vendor_name' => $r->vendor_name,
                'is_out' => (int) $r->stock <= 0,
            ]);
    }

    public function deadStock(DateRange $range): Collection
    {
        // Variant ids sold in the window (subquery), excluded from dead stock.
        $soldIds = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->whereBetween('o.created_at', [$range->from, $range->to])
            ->distinct()
            ->pluck('oi.product_variant_id');

        return DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('pv.stock', '>', 0)
            ->whereNotIn('pv.id', $soldIds)
            ->orderByDesc(DB::raw('pv.stock*pv.cost'))
            ->limit(self::CAP)
            ->get(['pv.sku', 'pv.stock', 'pv.cost', 'p.name_arabic', 'p.name_german', DB::raw("COALESCE(v.store_name_in_german, '(none)') as vendor_name")])
            ->map(fn ($r) => [
                'sku' => $r->sku,
                'product_name_arabic' => $r->name_arabic,
                'product_name_german' => $r->name_german,
                'stock' => (int) $r->stock,
                'value_at_cost' => round((float) $r->stock * (float) ($r->cost ?? 0), 2),
                'vendor_name' => $r->vendor_name,
            ]);
    }

    public function expiringStock(): Collection
    {
        $cutoff = Carbon::now()->addDays((int) config('telemetry.expiry_days_ahead', 30))->toDateString();

        return DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('pv.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('pv.stock', '>', 0)
            ->whereNotNull('pv.expiry_date')
            ->where('pv.expiry_date', '<=', $cutoff)
            ->orderBy('pv.expiry_date')
            ->limit(self::CAP)
            ->get(['pv.sku', 'pv.stock', 'pv.cost', 'pv.expiry_date', 'p.name_arabic', 'p.name_german', DB::raw("COALESCE(v.store_name_in_german, '(none)') as vendor_name")])
            ->map(fn ($r) => [
                'sku' => $r->sku,
                'product_name_arabic' => $r->name_arabic,
                'product_name_german' => $r->name_german,
                'stock' => (int) $r->stock,
                'expiry_date' => $r->expiry_date,
                'value_at_cost' => round((float) $r->stock * (float) ($r->cost ?? 0), 2),
                'vendor_name' => $r->vendor_name,
            ]);
    }
}
