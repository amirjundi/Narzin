<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Coupon;
use Modules\Checkout\Models\UserWallet;
use Modules\Checkout\Models\WalletTransaction;
use Modules\Vendor\Models\Vendor;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::with('vendor')->get();
        return view('admin::coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = Vendor::all();
        return view('admin::coupons.create' , compact('vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validatedData = Validator::make($request->all(), [
                'code' => 'required|unique:coupons',
                'discount_amount' => 'required|numeric',
                'discount_type' => 'required|in:percentage,fixed',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'usage_limit' => 'nullable|numeric',
                'minimum_cart_amount' => 'nullable|numeric',
                'is_active' => 'nullable|boolean',
                'vendor_id' => 'nullable',
            ])->validate();
            if($request->vendor_id == '' || $request->vendor_id == null){
                $validatedData['vendor_id'] = null;
            }

            $coupon = Coupon::create($validatedData);

            return redirect()->route('coupons.index')->with('success', 'coupon created successfully');
        }catch(ValidationException $e){
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $admin = Coupon::findOrFail($id);
        return view('admin::coupons.show' ,compact('admin'));
    }

    public function getApi(Request $request)
    {
        try {
            $coupons = Coupon::with('vendor')->where('code'  , $request->code)->first();
            if (!$coupons) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon not found or expired'
            ], 404);
            }
            return response()->json([
            'status' => true,
            'data' =>$coupons
            ]);
        } catch (Exception $e) {
            return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUserWalletData()
    {
        try {
            $wallet = UserWallet::where('user_id', Auth::id())->first();
            if (!$wallet) {
            $wallet = UserWallet::create([
                'user_id' => Auth::id(),
                'balance' => 0
            ]);
            }
            return response()->json([
            'status' => true,
            'data' => $wallet
            ]);
        } catch (Exception $e) {
            return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getWalletTransactions()
    {
        try {
            $transactions = WalletTransaction::where('user_id', Auth::id())->get();
            return response()->json([
            'status' => true,
            'data' => $transactions
            ]);
        } catch (Exception $e) {
            return response()->json([
            'status' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $vendors = Vendor::all();
        return view('admin::coupons.edit' , compact('coupon' , 'vendors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'code' => 'required|unique:coupons,code,' . $id,
                'discount_amount' => 'required|numeric',
                'discount_type' => 'required|in:percentage,fixed',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'usage_limit' => 'nullable|numeric',
                'minimum_cart_amount' => 'nullable|numeric',
                'is_active' => 'nullable|boolean',
                'vendor_id' => 'nullable|exists:vendors,id',
            ])->validate();

            $coupon = Coupon::findOrFail($id);
            $coupon->update($validatedData);

            return redirect()->route('coupons.index')->with('success', 'Coupon updated successfully');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
         $coupon = Coupon::findOrFail($id);
         $coupon->delete();
            return redirect()->route('coupons.index')->with('success', 'Coupon deleted successfully');
        }catch(Exception $e){
            return redirect()->route('coupons.index')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }
}
