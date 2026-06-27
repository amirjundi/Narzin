<?php

namespace Modules\UserAddress\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Models\DeliveryPrice;
use Modules\Admin\Models\PriceExchange;
use Modules\UserAddress\Models\UserAddress;

class UserAddressController extends Controller
{
    public function index()
    {
        try {
            $addresses = UserAddress::with(['deliveryZone'])
                ->where('user_id', Auth::id())
                ->get();

            return response()->json([
                'status' => true,
                'data' => $addresses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getDeliveryZones()
    {
        $zones = \Modules\Admin\Models\DeliveryZone::where('is_active', true)
            ->with(['deliveryMethods' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $zones
        ]);
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'city' => 'required|string',
                'title' => 'required|string',
                'is_default' => 'nullable|boolean',
                'phone_number' => 'required|string',
                'address' => 'required|string',
                'postal_code' => 'nullable|string',
                'delivery_zone_id' => 'required|exists:delivery_zones,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $address = UserAddress::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'city' => $request->city,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'delivery_zone_id' => $request->delivery_zone_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Address added successfully',
                'data' => $address->load(['deliveryZone'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $address = UserAddress::with(['deliveryZone'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {


            $validator = Validator::make($request->all(), [
                'city' => 'sometimes|required|string',
                'address' => 'sometimes|required|string',
                'postal_code' => 'nullable|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'delivery_zone_id' => 'sometimes|required|exists:delivery_zones,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $address = UserAddress::where('user_id', Auth::id())->find($id);
            if (!$address) {
                return response()->json([
                    'status' => false,
                    'message' => 'Address not found or unauthorized'
                ], 403);
            }

            $address->update($request->only([
                'city',
                'address',
                'postal_code',
                'latitude',
                'longitude',
                'delivery_zone_id'
            ]));



            return response()->json([
                'status' => true,
                'message' => 'Address updated successfully',
                'data' => $address->fresh(['country', 'city'])
            ]);
        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $address = UserAddress::where('user_id', Auth::id())->find($id);
            if (!$address) {
                return response()->json([
                    'status' => false,
                    'message' => 'Address not found or unauthorized'
                ], 403);
            }

            $address->delete();

            return response()->json([
                'status' => true,
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }





    public function setDefault($id)
    {
        $address = UserAddress::findOrFail($id);
        $user = User::findOrFail($address->user_id);
        if ($user->id != Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        // Ensure user can only modify their own addresses


        // Begin transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // First remove default status from all user addresses
            $addrsss = UserAddress::where('user_id', $user->id)
                ->update(['is_default' => false]);

            // Then set the selected one as default
            $address->is_default = true;
            $address->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Default address updated successfully',
                'data' => $address
            ]);
        } catch (\Exception $e) {
            Log::error('Set default address failed', ['error' => $e->getMessage()]);
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
