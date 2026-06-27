<?php

namespace Modules\VendorAccount\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ProductManagement\Models\Category;
use Modules\Vendor\Models\Vendor;

class VendorAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();

        return view('vendoraccount::index');
    }


    public function myProducts(){
        $myProducts = Category::with('products')->where('vendor_id', Auth::user()->vendor->id)->get();
        return view('vendoraccount::my-products', compact('myProducts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendoraccount::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('vendoraccount::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('vendoraccount::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
