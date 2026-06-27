<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\PlatformMarkup;

class PlatformMarkupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $markups = PlatformMarkup::latest('created_at')->paginate(15);
        return view('admin::platform-markup.index', compact('markups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::platform-markup.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        PlatformMarkup::create($validated);

        return redirect()->route('platform-markup.index')
            ->with('success', 'Global markup updated successfully!');
    }
}
