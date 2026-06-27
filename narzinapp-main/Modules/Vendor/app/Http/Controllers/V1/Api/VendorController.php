<?php

namespace Modules\Vendor\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Vendor\Services\VendorService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Modules\Checkout\Models\OrderItem;

class VendorController extends Controller
{
    protected $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $vendors = $this->vendorService->getPaginatedVendors();
            return response()->json([
                'status' => true,
                'data' => $vendors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_name_in_arabic' => 'required|string|max:255',
                'store_name_in_german' => 'required|string|max:255',
                'latitude' => 'nullable|string|max:255',
                'longitude' => 'nullable|string|max:255',
                'store_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|integer',
                'store_type' => 'nullable|string|max:50',
                'store_id' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $vendor = $this->vendorService->getVendorByUserId(Auth::id());
            if ($vendor->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You Already have a vendor account'
                ], 403);
            }

            $data = $request->except(['store_logo', 'store_id']);
            $data['user_id'] = Auth::id();

            // Handle file uploads
            if ($request->hasFile('store_logo')) {
                $data['store_logo'] = $request->file('store_logo')
                    ->store('vendors/logos', 'public');
            }

            if ($request->hasFile('store_id')) {
                $data['store_id'] = $request->file('store_id')
                    ->store('vendors/store_ids', 'public');
            }

            $vendor = $this->vendorService->createVendor($data);

            return response()->json([
                'status' => true,
                'message' => 'Vendor created successfully',
                'data' => $vendor
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $vendor = $this->vendorService->getVendorById($id);

            if (!$vendor) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $vendor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_name_in_arabic' => 'sometimes|required|string|max:255',
                'store_name_in_german' => 'sometimes|required|string|max:255',
                'latitude' => 'nullable|string|max:255',
                'longitude' => 'nullable|string|max:255',
                'store_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'store_type' => 'nullable|string|max:50',
                'store_id' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
                'markup_percentage' => 'nullable|numeric|min:0',
                'exchange_rate' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = $this->vendorService->getVendorById($id);
            if (!$vendor || $vendor->user_id !== Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized or vendor not found'
                ], 403);
            }
            // Initialize update data with request data
            // Only get the fields that were actually sent in the request
            $data = [];
            // Check each field individually
            if ($request->has('store_name_in_arabic')) {
                $data['store_name_in_arabic'] = $request->input('store_name_in_arabic');
            }
            if ($request->has(key: 'store_name_in_german')) {
                $data['store_name_in_german'] = $request->input('store_name_in_german');
            }
            if ($request->has('address')) {
                $data['address'] = $request->input('address');
            }
            if ($request->has('phone')) {
                $data['phone'] = $request->input('phone');
            }
            if ($request->has('store_type')) {
                $data['store_type'] = $request->input('store_type');
            }
            if ($request->has('markup_percentage')) {
                $data['markup_percentage'] = $request->input('markup_percentage');
            }
            if ($request->has('exchange_rate')) {
                $data['exchange_rate'] = $request->input('exchange_rate');
            }

            // Handle file uploads
            if ($request->hasFile('store_logo')) {
                if ($vendor->store_logo) {
                    Storage::disk('public')->delete($vendor->store_logo);
                }
                $data['store_logo'] = $request->file('store_logo')
                    ->store('vendors/logos', 'public');
            }

            if ($request->hasFile('store_id')) {
                if ($vendor->store_id) {
                    Storage::disk('public')->delete($vendor->store_id);
                }
                $data['store_id'] = $request->file('store_id')
                    ->store('vendors/store_ids', 'public');
            }

            // Debug log
            Log::info('Update data:', $data);
            $updatedVendor = $this->vendorService->updateVendor($id, $data);

            return response()->json([
                'status' => true,
                'message' => 'Vendor updated successfully',
                'data' => $updatedVendor
            ]);
        } catch (\Exception $e) {
            Log::error('Vendor update error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $vendor = $this->vendorService->getVendorById($id);
            if (!$vendor || $vendor->user_id !== Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized or vendor not found'
                ], 403);
            }

            $result = $this->vendorService->deleteVendor($id);

            return response()->json([
                'status' => true,
                'message' => 'Vendor deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    


    

}
