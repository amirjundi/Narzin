<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Models\Order;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\Vendor\Models\Vendor;
use Modules\Admin\Services\FunnelService;
use Modules\Admin\Services\AbandonedCartService;
use Modules\Admin\Services\AttributionService;
use Modules\Admin\Services\DiscountService;
use Modules\Admin\Services\ProfitService;
use Modules\Admin\Services\PaymentAnalyticsService;
use Modules\Admin\Services\ReturnAnalyticsService;
use Modules\Admin\Support\DateRange;

class StatisticsController extends Controller
{
    public function userStatistics()
    {
        // Basic user metrics
        $totalUsers = User::count();
        $activeUsers = Order::select('user_id')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereNotIn('user_id', function ($query) {
            $query->select('id')->from('vendors');
            })
            ->whereNotIn('user_id', function ($query) {
            $query->select('id')->from('users_admins');
            })
            ->distinct()
            ->get();

            $activeUsers = $activeUsers->count();   


        // Average order value
        $avgOrderValue = Order::avg('total_amount');

        // Calculate average lifetime value
        $avgLifetimeValue = Order::select(DB::raw('AVG(total_spent) as avg_lifetime_value'))
        ->fromSub(
            Order::select('user_id', DB::raw('SUM(total_amount) as total_spent'))
                ->groupBy('user_id'),
            'user_orders'
        )
        ->value('avg_lifetime_value');

        // User registration trends
        $userRegistrationTrend = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Users by language preference
        $usersByLanguage = User::select('preferred_language', DB::raw('COUNT(*) as count'))
            ->groupBy('preferred_language')
            ->get();

        // Top customers by total spend
        $topCustomers = User::select(
            'users.id',
            'users.name',
            'users.email',
            DB::raw('COUNT(orders.id) as orders_count'),
            DB::raw('SUM(orders.total_amount) as total_spent')
        )
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        // Popular categories
        $popularCategories = Category::select(
            'categories.name_arabic as name',
            DB::raw('COUNT(order_items.id) as purchase_count')
        )
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('categories.id', 'categories.name_arabic')
            ->orderBy('purchase_count', 'desc')
            ->limit(5)
            ->get();

        // Retention matrix
        $retentionMatrix = $this->calculateRetentionMatrix();

        return view('admin::statistics.users', compact(
            'totalUsers',
            'activeUsers',
            'avgOrderValue',
            'avgLifetimeValue',
            'userRegistrationTrend',
            'usersByLanguage',
            'topCustomers',
            'popularCategories',
            'retentionMatrix'
        ));
    }

    private function calculateRetentionMatrix()
    {
        $matrix = [];
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i <= 5; $i++) {
            $cohortDate = $startDate->copy()->addMonths($i);
            $cohortUsers = User::where('created_at', '>=', $cohortDate)
                ->where('created_at', '<', $cohortDate->copy()->addMonth())
                ->pluck('id');

            $retention = [];
            for ($j = 0; $j <= 5; $j++) {
                $monthStart = $cohortDate->copy()->addMonths($j);
                $monthEnd = $monthStart->copy()->addMonth();

                $activeUsers = Order::whereIn('user_id', $cohortUsers)
                    ->where('created_at', '>=', $monthStart)
                    ->where('created_at', '<', $monthEnd)
                    ->distinct('user_id')
                    ->count();

                $retention[] = $cohortUsers->count() > 0
                    ? round(($activeUsers / $cohortUsers->count()) * 100)
                    : 0;
            }

            $matrix[] = [
                'month' => $cohortDate->format('M Y'),
                'retention' => $retention
            ];
        }

        return $matrix;
    }


    public function vendorStatistics()
    {
        // Basic vendor metrics
        $totalVendors = Vendor::count();
        $activeVendors = Vendor::where('status', 'Active')->count();
        
        // Calculate growth rates
        $lastMonthVendors = Vendor::where('created_at', '<', Carbon::now()->subMonth())->count();
        $vendorGrowthRate = $lastMonthVendors > 0 
            ? round((($totalVendors - $lastMonthVendors) / $lastMonthVendors) * 100, 1)
            : 0;
        
        // Active vendor rate
        $activeVendorRate = $totalVendors > 0 
            ? round(($activeVendors / $totalVendors) * 100, 1)
            : 0;

        // Revenue metrics
        $totalRevenue = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.subtotal');
        
        $lastMonthRevenue = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '<', Carbon::now()->subMonth())
            ->sum('order_items.subtotal');
            
        $revenueGrowthRate = $lastMonthRevenue > 0
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Average order value
        $avgOrderValue = Order::avg('total_amount');
        $lastMonthAvgOrder = Order::where('created_at', '<', Carbon::now()->subMonth())
            ->avg('total_amount');
            
        $avgOrderGrowthRate = $lastMonthAvgOrder > 0
            ? round((($avgOrderValue - $lastMonthAvgOrder) / $lastMonthAvgOrder) * 100, 1)
            : 0;

        // Vendors by type
        $vendorsByType = Vendor::select('store_type', DB::raw('COUNT(*) as count'))
            ->groupBy('store_type')
            ->get();

        // Revenue trend
        $revenueTrend = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as amount')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performing vendors
        $topVendors = Vendor::select(
            'vendors.id',
            'vendors.store_name_in_arabic',
            'vendors.store_name_in_german',
            'vendors.store_type',
            'vendors.store_logo',
            'users.email',
            DB::raw('COUNT(DISTINCT orders.id) as order_count'),
            DB::raw('SUM(order_items.subtotal) as revenue')
        )
            ->leftJoin('order_items', 'vendors.id', '=', 'order_items.vendor_id')
            ->leftJoin('users', 'vendors.user_id', '=', 'users.id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('vendors.status', 'Active')
            ->groupBy('vendors.id', 'vendors.store_name_in_arabic', 'vendors.store_name_in_german', 
                     'vendors.store_type', 'vendors.store_logo', 'users.email')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($vendor) {
                // Calculate growth rate for each vendor
                $lastMonthRevenue = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->where('order_items.vendor_id', $vendor->id)
                    ->where('orders.created_at', '<', Carbon::now()->subMonth())
                    ->sum('order_items.subtotal');
                
                $vendor->growth_rate = $lastMonthRevenue > 0
                    ? round((($vendor->revenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                    : 0;
                
                return $vendor;
            });

        // Top categories
        $topCategories = Category::select(
            'categories.id',
            'categories.name_arabic',
            'categories.name_german',
            DB::raw('COUNT(DISTINCT vendors.id) as vendor_count')
        )
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->groupBy('categories.id', 'categories.name_arabic', 'categories.name_german')
            ->orderBy('vendor_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($category) use ($totalVendors) {
                $category->percentage = $totalVendors > 0 
                    ? round(($category->vendor_count / $totalVendors) * 100, 1)
                    : 0;
                return $category;
            });
        // Vendor locations for map
        $vendorLocations = Vendor::select(
            'store_name_in_arabic as store_name',
            'address',
            'store_type',
            'status',
            'latitude',
            'longitude'
        )->get();

        // Map center (average of all coordinates)
        $mapCenter = [
            'lat' => $vendorLocations->avg('latitude'),
            'lng' => $vendorLocations->avg('longitude')
        ];

        // Rating distribution
        $ratingDistribution = [0, 0, 0, 0, 0]; // Placeholder for actual rating data

        return view('admin::statistics.vendors', compact(
            'totalVendors',
            'activeVendors',
            'vendorGrowthRate',
            'activeVendorRate',
            'totalRevenue',
            'revenueGrowthRate',
            'avgOrderValue',
            'avgOrderGrowthRate',
            'vendorsByType',
            'revenueTrend',
            'topVendors',
            'topCategories',
            'vendorLocations',
            'mapCenter',
            'ratingDistribution'
        ));
    }


    public function orderStatistics()
    {
        // Basic order metrics
        $totalOrders = Order::count();
        $lastMonthOrders = Order::where('created_at', '<', Carbon::now()->subMonth())->count();
        
        // Calculate growth rates
        $orderGrowthRate = $lastMonthOrders > 0 
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        // Revenue metrics
        $totalRevenue = Order::sum('total_amount');
        $lastMonthRevenue = Order::where('created_at', '<', Carbon::now()->subMonth())
            ->sum('total_amount');
        
        $revenueGrowthRate = $lastMonthRevenue > 0
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Average order value metrics
        $avgOrderValue = Order::avg('total_amount');
        $lastMonthAvgOrder = Order::where('created_at', '<', Carbon::now()->subMonth())
            ->avg('total_amount');
        
        $avgOrderGrowthRate = $lastMonthAvgOrder > 0
            ? round((($avgOrderValue - $lastMonthAvgOrder) / $lastMonthAvgOrder) * 100, 1)
            : 0;

        // Fulfillment rate
        $completedOrders = Order::where('order_status', 'completed')->count();
        $fulfillmentRate = $totalOrders > 0 
            ? round(($completedOrders / $totalOrders) * 100, 1)
            : 0;

        // Orders by status
        $ordersByStatus = Order::select('order_status', DB::raw('COUNT(*) as count'))
            ->groupBy('order_status')
            ->get();

        // Shipping types distribution
        $shippingTypes = Order::select('shipping_type', DB::raw('COUNT(*) as count'))
            ->groupBy('shipping_type')
            ->get();

        // Order trends with revenue
        $orderTrends = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as revenue')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent orders
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Popular products
        // $popularProducts = Product::select(
        //     'products.id',
        //     'products.name_arabic',
        //     'products.name_german',
        //     'products.image',
        //     DB::raw('COUNT(order_items.id) as total_sold'),
        //     DB::raw('SUM(order_items.subtotal) as total_revenue')
        // )
        //     ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
        //     ->groupBy('products.id', 'products.name_arabic', 'products.name_german', 'products.image')
        //     ->orderBy('total_sold', 'desc')
        //     ->limit(5)
        //     ->get()
        //     ->map(function ($product) {
        //         // Calculate growth rate for each product
        //         $lastMonthRevenue = OrderItem::where('product_id', $product->id)
        //             ->whereHas('order', function ($query) {
        //                 $query->where('created_at', '<', Carbon::now()->subMonth());
        //             })
        //             ->sum('subtotal');
                
        //         $product->growth_rate = $lastMonthRevenue > 0
        //             ? round((($product->total_revenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
        //             : 0;
                
        //         return $product;
        //     });
        //     // Popular products with images
            $popularProducts = Product::select(
                'products.id',
                'products.name_arabic',
                'products.name_german',
                DB::raw('COUNT(order_items.id) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
                ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->groupBy('products.id', 'products.name_arabic', 'products.name_german')
                ->orderBy('total_sold', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($product) {
                // Calculate growth rate for each product
                $lastMonthRevenue = OrderItem::where('product_id', $product->id)
                    ->whereHas('order', function ($query) {
                    $query->where('created_at', '<', Carbon::now()->subMonth());
                    })
                    ->sum('subtotal');
                
                $product->growth_rate = $lastMonthRevenue > 0
                    ? round((($product->total_revenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                    : 0;

                // Get product images
                $product->images = DB::table('products_images')
                    ->where('product_id', $product->id)
                    ->pluck('image');
                
                return $product;
                });



        return view('admin::statistics.orders', compact(
            'totalOrders',
            'orderGrowthRate',
            'totalRevenue',
            'revenueGrowthRate',
            'avgOrderValue',
            'avgOrderGrowthRate',
            'fulfillmentRate',
            'ordersByStatus',
            'shippingTypes',
            'orderTrends',
            'recentOrders',
            'popularProducts',
        ));
    }

    public function productStatistics()
    {
        // Products by category
        $productsByCategory = Product::select('categories.name_arabic', 'categories.name_german', DB::raw('COUNT(products.id) as count'))
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name_arabic', 'categories.name_german')
            ->get();

        // Top selling products
        $topProducts = OrderItem::select(
            'products.name_arabic',
            'products.name_german',
            DB::raw('SUM(order_items.quantity) as total_sold')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name_arabic', 'products.name_german')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        // Stock status overview
        $stockStatus = ProductVariant::select(
            DB::raw('CASE 
                WHEN stock = 0 THEN "Out of Stock"
                WHEN stock < 10 THEN "Low Stock"
                ELSE "In Stock"
            END as stock_status'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('stock_status')
            ->get();

        return view('admin::statistics.products', compact(
            'productsByCategory',
            'topProducts',
            'stockStatus'
        ));
    }

    public function funnelStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $funnel = (new FunnelService())->funnel($range);
        $abandoned = (new AbandonedCartService())->abandoned($range);
        $attribution = [
            'byChannel' => (new AttributionService())->byChannel($range),
            'byCampaign' => (new AttributionService())->byCampaign($range),
        ];

        return view('admin::statistics.funnel', [
            'funnel' => $funnel,
            'abandoned' => $abandoned,
            'attribution' => $attribution,
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }

    public function promotionStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $service = new DiscountService();

        return view('admin::statistics.promotions', [
            'coupons' => $service->byCoupon($range),
            'promotions' => $service->byPromotion($range),
            'summary' => $service->summary($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }

    public function profitStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);

        return view('admin::statistics.profit', [
            'profit' => (new ProfitService())->summary($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }

    public function paymentStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $service = new PaymentAnalyticsService();

        return view('admin::statistics.payments', [
            'orderSummary' => $service->orderPaymentSummary($range),
            'methodMix' => $service->methodMix($range),
            'attempts' => $service->attemptSummary($range),
            'failureReasons' => $service->failureReasons($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }

    public function returnStatistics(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $service = new ReturnAnalyticsService();

        return view('admin::statistics.returns', [
            'summary' => $service->summary($range),
            'byReason' => $service->byReason($range),
            'from' => $range->from->toDateString(),
            'to' => $range->to->toDateString(),
        ]);
    }
}
