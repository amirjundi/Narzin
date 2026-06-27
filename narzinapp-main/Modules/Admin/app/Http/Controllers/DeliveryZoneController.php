<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\DeliveryZone;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        $zones = DeliveryZone::with('deliveryMethods')->latest()->paginate(20);
        return view('admin::delivery-zones.index', compact('zones'));
    }

    public function create()
    {
        return view('admin::delivery-zones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_english' => 'required|string|max:255|unique:delivery_zones',
            'name_german' => 'required|string|max:255|unique:delivery_zones',
            'name_arabic' => 'required|string|max:255|unique:delivery_zones',
            'is_active' => 'boolean',
        ]);

        DeliveryZone::create([
            'name_english' => $request->name_english,
            'name_german' => $request->name_german,
            'name_arabic' => $request->name_arabic,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('delivery-zones.index')->with('success', 'Zone created successfully');
    }

    public function show(DeliveryZone $deliveryZone)
    {
        $deliveryZone->load('deliveryMethods');
        return view('admin::delivery-zones.show', compact('deliveryZone'));
    }

    public function edit(DeliveryZone $deliveryZone)
    {
        return view('admin::delivery-zones.edit', compact('deliveryZone'));
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        $request->validate([
            'name_english' => 'required|string|max:255|unique:delivery_zones,name_english,' . $deliveryZone->id,
            'name_german' => 'required|string|max:255|unique:delivery_zones,name_german,' . $deliveryZone->id,
            'name_arabic' => 'required|string|max:255|unique:delivery_zones,name_arabic,' . $deliveryZone->id,
            'is_active' => 'boolean',
        ]);

        $deliveryZone->update([
            'name_english' => $request->name_english,
            'name_german' => $request->name_german,
            'name_arabic' => $request->name_arabic,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('delivery-zones.index')->with('success', 'Zone updated successfully');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        $deliveryZone->delete();
        return redirect()->route('delivery-zones.index')->with('success', 'Zone deleted successfully');
    }
}
