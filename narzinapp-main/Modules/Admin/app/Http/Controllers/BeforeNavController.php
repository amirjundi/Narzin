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
use Modules\Admin\Models\BeforeNav;
use Modules\Admin\Models\UserAdmin;
use Modules\Vendor\Models\Vendor;
use PHPUnit\Metadata\Before;

class BeforeNavController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $texts = BeforeNav::all();
        return view('admin::before-nav.index', compact('texts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::before-nav.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'text' => 'required|string|max:255',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            BeforeNav::create([
                'text' => $request->text,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            DB::commit();
            return redirect()->route('before-nav.index')->with('success', 'before-nav text  created successfully');
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
        return view('admin::before-nav.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $beforeNav = BeforeNav::findOrFail($id);
        return view('admin::before-nav.edit', compact('beforeNav'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|max:255',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            $beforeNav = BeforeNav::find($id);
            if (!$beforeNav) {
                return redirect()->back()->with('error', 'record not found');
            }

            $beforeNav->updated([
                'text' => $request->text,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
            return redirect()->route('before-nav.index')->with('success', 'Before Nav Text Updated Successfully');
        } catch (Exception $e) {
            return redirect()->route('before-nav.index')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $beforeNav = BeforeNav::find($id);
            $beforeNav->delete();
            return redirect()->route('before-nav.index')->with('success', 'Before Nav Text deleted successfully');
        } catch (Exception $e) {
            return redirect()->route('before-nav.index')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }
}
