<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\DeliveryMethod;
use Modules\Admin\Models\DeliveryZone;

class DeliveryMethodController extends Controller
{
    public function store(Request $request, DeliveryZone $deliveryZone)
    {
        $request->validate([
            'name_english' => 'required|string|max:255',
            'name_german' => 'required|string|max:255',
            'name_arabic' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
            'estimated_days' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $deliveryZone->deliveryMethods()->create([
            'name_english' => $request->name_english,
            'name_german' => $request->name_german,
            'name_arabic' => $request->name_arabic,
            'base_price' => $request->base_price,
            'price_per_kg' => $request->price_per_kg,
            'estimated_days' => $request->estimated_days,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('delivery-zones.show', $deliveryZone)->with('success', 'Delivery method added successfully');
    }

    public function destroy(DeliveryZone $deliveryZone, DeliveryMethod $deliveryMethod)
    {
        $deliveryMethod->delete();
        return redirect()->route('delivery-zones.show', $deliveryZone)->with('success', 'Delivery method deleted successfully');
    }
}
