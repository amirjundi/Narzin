<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Models\City;
use Modules\Admin\Models\Country;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\UserWallet;
use Modules\Checkout\Models\WalletTransaction;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::whereDoesntHave('admin')
            ->whereDoesntHave('vendor')
            ->get();

        return view('admin::users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validatedData = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ])->validate();

            $validatedData['password'] = Hash::make($validatedData['password']);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'email_verified_at' => Carbon::now(),
                'password' => $validatedData['password'],
                ]
            );

            return redirect()->route('users.index')->with('success', 'User created successfully');
        }catch(ValidationException $e){
            return redirect()->back()->withErrors($e->errors());
        }

    }

    public function updateWallet(Request $request){
        try{
            $validatedData = Validator::make($request->all(), [
                'operation' => 'required|in:add,subtract',
                'amount' => 'required|numeric',
                'user_id' => 'required|exists:users,id',
            ])->validate();
            

            $wallet = UserWallet::firstOrCreate([
                'user_id' => $request->user,
            ]);
          
            if($validatedData['operation'] == 'add'){
                $wallet->balance += $validatedData['amount'];
                WalletTransaction::create([
                    'user_id' => $request->user,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $validatedData['amount']
                ]);
                
            }else{
                $wallet->balance -= $validatedData['amount'];

                WalletTransaction::create([
                    'user_id' => $request->user,
                    'wallet_id' => $wallet->id,
                    'type' => 'withdraw',
                    'amount' => $validatedData['amount']
                ]);
            }
            $wallet->save();

            
            return redirect()->back()->with('success' , 'Balance updated successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error' , 'Something went wrong' . $e->getMessage());
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $user = User::with('wallet' , 'orders' , 'address')->findOrFail($id);
        $countries = Country::all();
        $cities = City::all();
        return view('admin::users.show' ,compact('user' , 'countries' , 'cities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin::users.edit' , compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|min:6',
            ])->validate();





            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }

            $user = User::findOrFail($id);
            $user->update($validatedData);
    
            return redirect()->route('users.index')->with('success', 'User updated successfully');
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
            $admin = User::findOrFail($id);

            $admin->delete();
            return redirect()->route('admins.index')->with('success', 'User deleted successfully');
        }catch(Exception $e){
            return redirect()->route('admins.index')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }
}
