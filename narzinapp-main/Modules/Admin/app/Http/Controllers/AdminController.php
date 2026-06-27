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
use Modules\Admin\Models\UserAdmin;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = User::whereHas('admin', function($query) {
            
        })
        ->with('admin')
        ->get();
        return view('admin::admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::admins.create');
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
            UserAdmin::create([
                'user_id' => $user->id,
                'is_active' => 1,
            ]);

            return redirect()->route('admins.index')->with('success', 'Admin created successfully');
        }catch(ValidationException $e){
            return redirect()->back()->withErrors($e->errors());
        }

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $admin = User::findOrFail($id);
        return view('admin::admins.show' ,compact('admin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $admin = User::with('admin')->findOrFail($id);
        return view('admin::admins.edit' , compact('admin'));
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
                'is_active' => 'nullable|boolean',
            ])->validate();


            $admin = User::findOrFail($id);
            
            if(!isset($validatedData['is_active'])){
                $admin->admin->is_active = 0;
                $admin->admin->save();
            }else{
                $admin->admin->is_active = 1;
                $admin->admin->save();
            }


            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }

            $admin->update($validatedData);

            return redirect()->route('admins.index')->with('success', 'Admin updated successfully');
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
            $userId = UserAdmin::where('user_id', $id)->first();

            $userId->delete();
            $admin->delete();
            return redirect()->route('admins.index')->with('success', 'Admin deleted successfully');
        }catch(Exception $e){
            return redirect()->route('admins.index')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }
}
