<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\Vendor\Models\Vendor;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::with('user')->where('status' , 'Active')->get();
        return view('admin::vendors.index', compact('vendors'));
    }


    public function indexNotActive()
    {
        $vendors = Vendor::with('user')->where('status' , 'Waiting Approve')->get();
        return view('admin::vendors.indexNotActive', compact('vendors'));
    }


    public function vendorChangeStatues(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Active,Waiting Approve,Rejected',
        ]);
        $vendor = Vendor::where('id', $id)->first();

        if ($vendor) {
            $vendor->update([
                'status' => $request->status,
            ]);
            return redirect()->route('vendors.index')->with('success', 'Vendor status updated successfully');
        } else {
            return redirect()->route('vendors.index')->with('error', 'Vendor not found');
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::vendors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'store_name_in_arabic' => 'required|string|max:255',
                'store_name_in_german' => 'required|string|max:255',
                'latitude' => 'nullable|string|max:255',
                'longitude' => 'nullable|string|max:255',
                'phone' => 'required|string',
                'Store_type' => 'required|in:Grocery,Pharmacy,Restaurant',
                'store_logo' => 'required|image|max:2048',
                'store_id' => 'required|file|mimes:pdf,jpg,jpeg,png',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($validated['password']),
                'role' => 'vendor',
            ]);

            $logoPath = $request->file('store_logo')->store('vendors/logos', 'public');
            $storeIdPath = $request->file('store_id')->store('vendors/store_ids', 'public');

            $store = Vendor::create([
                'user_id' => $user->id,
                'store_name_in_arabic' => $validated['store_name_in_arabic'],
                'store_name_in_german' => $validated['store_name_in_german'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'phone' => $validated['phone'],
                'store_type' => $validated['Store_type'],
                'store_logo' => $logoPath,
                'store_id' => $storeIdPath,
                'status' => 'Active',
            ]);

            DB::commit();
            return redirect()->route('vendors.index')->with('success', 'Vendor and store created successfully');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Database error occurred' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'UnExpected error occurred' . $e->getMessage());
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request , $id)
    {
        // Get the vendor
        $user = Auth::user();
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return redirect()->route('vendors.index')->with('error', 'Vendor not found');
        }
        // Get order items with filtering
        $orderItems = OrderItem::where('vendor_id', $vendor->id)
            ->when($request->get('status') && $request->get('status') != 'all', function($query) use ($request) {
                return $query->where('status', $request->get('status'));
            })
            ->with(['order', 'product', 'product.images', 'productVariant', 
                   'productVariant.variantValues', 'productVariant.variantValues.variantAttribute'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        // Get products with filtering
        $products = Product::where('vendor_id', $vendor->id)
            ->when($request->get('search'), function($query) use ($request) {
                return $query->where(function($q) use ($request) {
                    $q->where('name_german', 'like', '%' . $request->get('search') . '%')
                      ->orWhere('name_arabic', 'like', '%' . $request->get('search') . '%');
                });
            })
            ->when($request->get('category'), function($query) use ($request) {
                return $query->where('category_id', $request->get('category'));
            })
            ->when($request->get('product_status') === 'active', function($query) {
                return $query->where('is_active', true);
            })
            ->when($request->get('product_status') === 'inactive', function($query) {
                return $query->where('is_active', false);
            })
            ->when($request->get('stock_status') === 'in_stock', function($query) {
                return $query->whereHas('variants', function($q) {
                    $q->where('stock', '>', 0)->where('is_out_of_stock', false);
                });
            })
            ->when($request->get('stock_status') === 'out_of_stock', function($query) {
                return $query->whereDoesntHave('variants', function($q) {
                    $q->where('stock', '>', 0)->where('is_out_of_stock', false);
                });
            })
            ->with(['category', 'child_category', 'images', 'variants', 'variants.variantValues'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        
        // Categories for filtering
        $categories = Category::all();
        
        // Calculate statistics
        $stats = $this->getStatistics($vendor->id);
        
        // Get chart data
        $revenueChart = $this->getRevenueChartData($vendor->id);
        $categoryChart = $this->getCategoryChartData($vendor->id);
        
        // Get top products
        $topProducts = $this->getTopProducts($vendor->id);
        
        // Get monthly reports
        $monthlyReports = $this->getMonthlyReports($vendor->id);
        
        return view('admin::vendors.show', compact(
            'vendor', 'orderItems', 'products', 'categories',
            'stats', 'revenueChart', 'categoryChart', 'topProducts', 'monthlyReports'
        ));
    }
    
    /**
     * Calculate vendor statistics.
     *
     * @param int $vendorId
     * @return array
     */
/**
 * Calculate vendor statistics.
 *
 * @param int $vendorId
 * @return array
 */
private function getStatistics($vendorId)
{
    // Calculate dates for current and previous periods
    $currentMonthStart = Carbon::now()->startOfMonth();
    $currentMonthEnd = Carbon::now()->endOfMonth();
    $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
    $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
    
    // Get current month stats
    $currentRevenue = OrderItem::where('vendor_id', $vendorId)
        ->whereHas('order', function ($query) {
            $query->where('order_status', 'shipped');
        })
        ->whereBetween('order_items.created_at', [$currentMonthStart, $currentMonthEnd])
        ->sum('subtotal');
        
    $currentOrders = OrderItem::where('vendor_id', $vendorId)
        ->whereBetween('order_items.created_at', [$currentMonthStart, $currentMonthEnd])
        ->count();
        
    $currentCost = OrderItem::where('order_items.vendor_id', $vendorId)
        ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
        ->sum(DB::raw('order_items.quantity * COALESCE(product_variants.cost, 0)'));
    
    // Get previous month stats
    $previousRevenue = OrderItem::where('vendor_id', $vendorId)
        ->whereBetween('order_items.created_at', [$previousMonthStart, $previousMonthEnd])
        ->sum('subtotal');
        
    $previousOrders = OrderItem::where('vendor_id', $vendorId)
        ->whereBetween('order_items.created_at', [$previousMonthStart, $previousMonthEnd])
        ->count();
        
    $previousCost = OrderItem::where('order_items.vendor_id', $vendorId)
        ->whereBetween('order_items.created_at', [$previousMonthStart, $previousMonthEnd])
        ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
        ->sum(DB::raw('order_items.quantity * COALESCE(product_variants.cost, 0)'));
    
    // Calculate trend percentages
    $revenueTrend = $previousRevenue > 0 
        ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 
        : 0;
        
    $ordersTrend = $previousOrders > 0 
        ? (($currentOrders - $previousOrders) / $previousOrders) * 100 
        : 0;
        
    $costTrend = $previousCost > 0 
        ? (($currentCost - $previousCost) / $previousCost) * 100 
        : 0;
        
    // Calculate total and profit
    $totalRevenue = OrderItem::where('vendor_id', $vendorId)->sum('subtotal');
    $totalCost = OrderItem::where('order_items.vendor_id', $vendorId)
        ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
        ->sum(DB::raw('order_items.quantity * COALESCE(product_variants.cost, 0)'));
    $totalOrders = OrderItem::where('vendor_id', $vendorId)->count();
    $netProfit = $totalRevenue - $totalCost;
    
    $currentProfit = $currentRevenue - $currentCost;
    $previousProfit = $previousRevenue - $previousCost;
    $profitTrend = $previousProfit != 0 
        ? (($currentProfit - $previousProfit) / abs($previousProfit)) * 100 
        : 0;
    
    return [
        'total_revenue' => $totalRevenue,
        'total_cost' => $totalCost,
        'total_orders' => $totalOrders,
        'net_profit' => $netProfit,
        'revenue_trend' => round($revenueTrend, 1),
        'orders_trend' => round($ordersTrend, 1),
        'cost_trend' => round($costTrend, 1),
        'profit_trend' => round($profitTrend, 1)
    ];
}
    
/**
 * Get revenue chart data.
 *
 * @param int $vendorId
 * @return array
 */
private function getRevenueChartData($vendorId)
{
    // Get data for the last 30 days
    $startDate = Carbon::now()->subDays(30)->startOfDay();
    $endDate = Carbon::now()->endOfDay();
    
    // Prepare date ranges
    $dates = [];
    $currentDate = $startDate->copy();
    while ($currentDate->lte($endDate)) {
        $dates[] = $currentDate->format('Y-m-d');
        $currentDate->addDay();
    }
    
    // Get revenue data by day
    $revenueData = OrderItem::where('order_items.vendor_id', $vendorId)
        ->whereBetween('order_items.created_at', [$startDate, $endDate])
        ->select(
            DB::raw('DATE(order_items.created_at) as date'),
            DB::raw('SUM(order_items.subtotal) as revenue')
        )
        ->groupBy('date')
        ->pluck('revenue', 'date')
        ->toArray();
    
    // Get cost data by day
    $costData = OrderItem::where('order_items.vendor_id', $vendorId)
        ->whereBetween('order_items.created_at', [$startDate, $endDate])
        ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
        ->select(
            DB::raw('DATE(order_items.created_at) as date'),
            DB::raw('SUM(order_items.quantity * COALESCE(product_variants.cost, 0)) as cost')
        )
        ->groupBy('date')
        ->pluck('cost', 'date')
        ->toArray();
    
    // Prepare data for chart
    $revenue = [];
    $cost = [];
    $profit = [];
    
    foreach ($dates as $date) {
        $rev = $revenueData[$date] ?? 0;
        $cst = $costData[$date] ?? 0;
        
        $revenue[] = $rev;
        $cost[] = $cst;
        $profit[] = $rev - $cst;
    }
    
    // Format dates for display
    $formattedDates = array_map(function($date) {
        return Carbon::parse($date)->format('M d');
    }, $dates);
    
    return [
        'labels' => $formattedDates,
        'revenue' => $revenue,
        'cost' => $cost,
        'profit' => $profit
    ];
}
    
    /**
     * Get revenue by category data.
     *
     * @param int $vendorId
     * @return array
     */
    private function getCategoryChartData($vendorId)
    {
        $categoryData = OrderItem::where('order_items.vendor_id', $vendorId)
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name_german as category',
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->groupBy('categories.id', 'categories.name_german')
            ->orderBy('revenue', 'desc')
            ->get();
        
        $labels = $categoryData->pluck('category')->toArray();
        $data = $categoryData->pluck('revenue')->toArray();
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Get top selling products.
     *
     * @param int $vendorId
     * @return \Illuminate\Support\Collection
     */
    private function getTopProducts($vendorId)
    {
        $totalRevenue = OrderItem::where('vendor_id', $vendorId)->sum('subtotal') ?: 1;
        
        return OrderItem::where('order_items.vendor_id', $vendorId)
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('(SUM(order_items.subtotal) / ' . $totalRevenue . ') * 100 as percentage')
            )
            ->with('product', 'product.images')
            ->groupBy('order_items.product_id')
            ->orderBy('total_revenue', 'desc')
            ->take(5)
            ->get();
    }
    
/**
 * Get monthly financial reports.
 *
 * @param int $vendorId
 * @return \Illuminate\Support\Collection
 */
private function getMonthlyReports($vendorId)
{
    return DB::table('order_items')
        ->where('order_items.vendor_id', $vendorId)
        ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
        ->select(
            DB::raw('DATE_FORMAT(order_items.created_at, "%b %Y") as month'),
            DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count'),
            DB::raw('SUM(order_items.subtotal) as revenue'),
            DB::raw('SUM(order_items.quantity * COALESCE(product_variants.cost, 0)) as cost'),
            DB::raw('SUM(order_items.subtotal) - SUM(order_items.quantity * COALESCE(product_variants.cost, 0)) as profit')
        )
        ->groupBy('month')
        ->orderBy(DB::raw('MIN(order_items.created_at)'), 'desc')
        ->take(6)
        ->get();
}
    
    /**
     * Get revenue data for AJAX request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRevenueData(Request $request)
    {
        $user = Auth::user();
        $vendor = Vendor::where('user_id', $user->id)->firstOrFail();
        $period = $request->get('period', 'month');
        
        // Logic to get revenue data based on period
        $revenueData = $this->getRevenueChartData($vendor->id, $period);
        
        return response()->json($revenueData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $vendor = Vendor::with('user')->where('id' ,$id)->first();
        return view('admin::vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $vendor = Vendor::with('user')->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $vendor->user->id,
                'password' => 'nullable|min:8',
                'status' => 'required|in:Active,Waiting Approve,Rejected',
                'store_name_in_arabic' => 'required|string|max:255',
                'store_name_in_german' => 'required|string|max:255',
                'latitude' => 'nullable|string|max:255',
                'longitude' => 'nullable|string|max:255',
                'phone' => 'required|string',
                'Store_type' => 'required|in:Grocery,Pharmacy,Restaurant',
                'store_logo' => 'nullable|image|max:2048',
                'store_id' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
                'markup_percentage' => 'nullable|numeric|min:0|max:100',
                'exchange_rate' => 'nullable|numeric|min:0',
                'commission_percentage' => 'nullable|numeric|min:0|max:100',
                'discount_absorption_percentage' => 'nullable|numeric|min:0|max:100',
            ]);

            $vendor->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : $vendor->user->password,
            ]);

            $vendor = Vendor::where('user_id', $vendor->user->id)->firstOrFail();

            if ($request->hasFile('store_logo')) {
                if ($vendor->store_logo) {
                    Storage::disk('public')->delete($vendor->store_logo);
                }
                $logoPath = $request->file('store_logo')->store('vendors/logos', 'public');
                $vendor->store_logo = $logoPath;
            }

            if ($request->hasFile('store_id')) {
                if ($vendor->store_id) {
                    Storage::disk('public')->delete($vendor->store_id);
                }
                $storeIdPath = $request->file('store_id')->store('vendors/store_ids', 'public');
                $vendor->store_id = $storeIdPath;
            }

            $vendor->update([
                'store_name_in_arabic' => $validated['store_name_in_arabic'],
                'store_name_in_german' => $validated['store_name_in_german'],
                'latitude' => $validated['latitude'] ?? $vendor->latitude,
                'longitude' => $validated['longitude'] ?? $vendor->longitude,
                'phone' => $validated['phone'],
                'status' => $validated['status'],
                'store_type' => $validated['Store_type'],
                'markup_percentage' => $validated['markup_percentage'] ?: null,
                'exchange_rate' => $validated['exchange_rate'] ?: null,
                'commission_percentage' => $validated['commission_percentage'] ?: null,
                'discount_absorption_percentage' => $validated['discount_absorption_percentage'] ?: null,
            ]);

            DB::commit();
            return redirect()->route('vendors.index')->with('success', 'Vendor and store updated successfully');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Database error occurred' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Unexpected error occurred' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $vendor = User::findOrFail($id);
            $userId = Vendor::where('user_id', $id)->first();
            if ($vendor->store_logo) {
                Storage::disk('public')->delete($vendor->store_logo);
            }
            if ($vendor->store_id) {
                Storage::disk('public')->delete($vendor->store_id);
            }
            $userId->delete();
            $vendor->delete();
            return redirect()->route('vendors.index')->with('success', 'vendor deleted successfully');
        } catch (Exception $e) {
            return redirect()->route('vendors.index')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }
}
