<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Checkout\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::latest('id')->get();
        return view('admin::promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin::promotions.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Promotion::create($data);
        return redirect()->route('promotions.index')->with('success', 'Promotion created.');
    }

    public function edit($id)
    {
        $promotion = Promotion::findOrFail($id);
        return view('admin::promotions.edit', compact('promotion'));
    }

    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->update($this->validateData($request));
        return redirect()->route('promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy($id)
    {
        Promotion::findOrFail($id)->delete();
        return redirect()->route('promotions.index')->with('success', 'Promotion deleted.');
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:free_shipping,percentage,fixed',
            'value' => 'nullable|numeric|min:0|required_if:type,percentage,fixed',
            'minimum_cart_amount' => 'required|numeric|min:0',
            'absorbed_by_vendor_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ];
        if ($request->input('type') === 'percentage') {
            $rules['value'] = 'required|numeric|min:0|max:100';
        }

        $data = $request->validate($rules);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        if ($data['type'] === 'free_shipping') {
            $data['value'] = null;
            $data['absorbed_by_vendor_percentage'] = 0;
        }

        return $data;
    }
}
