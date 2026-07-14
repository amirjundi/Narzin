<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\PriceExchange;

class PriceExchangeController extends Controller
{
    public function index()
    {
        $prices = PriceExchange::latest()->paginate(10);
        return view('admin::price-exchange.index', compact('prices'));
    }

    public function create()
    {
        return view('admin::price-exchange.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'price_rate' => 'required|numeric|min:1',
        ]);

        PriceExchange::create($validated);

        return redirect()->route('price-exchange.index')
                         ->with('success', 'Exchange rate updated successfully!');
    }
}