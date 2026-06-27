<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Models\Banner;
use Modules\Admin\Models\UserAdmin;
use Modules\Vendor\Models\Vendor;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::all();
        return view('admin::banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'image' => 'required|image|max:2048',
            ]);
            if (!$request->has('is_mobile')) {
                $request->merge(['is_mobile' => 0]);
            }

            


            $banner = $request->file('image')->store('bannersImages', 'public');

             Banner::create([
                'image' => $banner,
                'title' => $request->title,
                'description' => $request->description,
                'is_mobile' => $request->is_mobile,
            ]);

            DB::commit();
            return redirect()->route('banners.index')->with('success', 'Banner  created successfully');
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
    public function show($id)
    {
        return view('admin::banners.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin::banners.edit', compact('banner'));
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
