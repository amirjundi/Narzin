<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Models\UserAdmin;
use Modules\UserAddress\Models\UserAddress;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
            'city_id' => 'required|exists:cites,id',
            'postal_code' => 'required|string|max:20',
            'phone_number' => 'required|string|max:20',
            'title' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        
        // Check if this is the user's first address
        $isFirstAddress = $user->address->isEmpty();
        
        $address = new UserAddress();
        $address->user_id = $user->id;
        $address->address = $request->address;
        $address->city_id = $request->city_id;
        $address->postal_code = $request->postal_code;
        $address->phone_number = $request->phone_number;
        $address->title = $request->title;
        
        // Set default coordinates if not provided
        if (!$request->has('latitude') || !$request->has('longitude')) {
            $address->latitude = 0;
            $address->longitude = 0;
        } else {
            $address->latitude = $request->latitude;
            $address->longitude = $request->longitude;
        }
        
        $address->save();
        
        // If this is the first address, make it the default
        if ($isFirstAddress) {
            // Logic to mark as default could be implemented here
            // For example, you might have a user_default_address table or a is_default flag
        }
        
        return redirect()->back()->with('success', 'Address added successfully.');
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
            'city_id' => 'required|exists:cites,id',
            'postal_code' => 'required|string|max:20',
            'phone_number' => 'required|string|max:20',
            'title' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $address = UserAddress::findOrFail($id);
        
        // Ensure user can only update their own addresses
        
        $address->address = $request->address;
        $address->city_id = $request->city_id;
        $address->postal_code = $request->postal_code;
        $address->phone_number = $request->phone_number;
        $address->title = $request->title;
        
        if ($request->has('latitude') && $request->has('longitude')) {
            $address->latitude = $request->latitude;
            $address->longitude = $request->longitude;
        }
        
        $address->save();
        
        return redirect()->back()->with('success', 'Address updated successfully.');
    }


    public function destroy($id)
    {
        $address = UserAddress::findOrFail($id);
        
        
        // Check if this is the only address
        $user = Auth::user();
        if ($user->address->count() <= 1) {
            return redirect()->back()->with('error', 'Cannot delete your only address.');
        }
        
        // Implement logic to ensure we're not deleting the default address
        // or to reassign a new default if needed
        
        $address->delete();
        
        return redirect()->back()->with('success', 'Address deleted successfully.');
    }
    
    public function setDefault($id)
    {
        $address = UserAddress::findOrFail($id);
        $user = User::findOrFail($address->user_id);
        
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
            return redirect()->back()->with('success', 'Default address updated.');
            
        } catch (\Exception $e) {
            Log::error('Set default address failed', ['error' => $e->getMessage()]);
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update default address.');
        }
    }

}
