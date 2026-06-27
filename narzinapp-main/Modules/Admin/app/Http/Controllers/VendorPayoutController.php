<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Models\PayoutSetting;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Models\VendorPayout;
use Modules\Vendor\Models\VendorTransaction;
use Modules\Vendor\Services\VendorLedgerService;

class VendorPayoutController extends Controller
{
    public function __construct(private VendorLedgerService $ledger)
    {
    }

    public function index()
    {
        $vendors = Vendor::all()->map(function ($v) {
            return [
                'vendor' => $v,
                'pending' => $this->ledger->pendingEarnings($v->id),
                'payable' => $this->ledger->payableBalance($v->id),
                'paid' => $this->ledger->totalPaid($v->id),
            ];
        });
        return view('admin::vendor-payouts.index', compact('vendors'));
    }

    public function show($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $entries = VendorTransaction::where('vendor_id', $vendorId)->latest('id')->get();
        $payable = $this->ledger->payableBalance($vendorId);
        return view('admin::vendor-payouts.show', compact('vendor', 'entries', 'payable'));
    }

    public function payout(Request $request, $vendorId)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        try {
            $this->ledger->recordPayout($vendorId, (float) $data['amount'], $data['method'] ?? null, $data['reference'] ?? null, $data['notes'] ?? null, Auth::id());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
        return redirect()->route('vendor-payouts.show', $vendorId)->with('success', 'Payout recorded.');
    }

    public function adjust(Request $request, $vendorId)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'description' => 'required|string|max:255',
        ]);
        $this->ledger->adjust($vendorId, (float) $data['amount'], $data['description'], Auth::id());
        return redirect()->route('vendor-payouts.show', $vendorId)->with('success', 'Adjustment recorded.');
    }

    public function settings()
    {
        $settings = PayoutSetting::current();
        return view('admin::vendor-payouts.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'default_commission_percentage' => 'required|numeric|min:0|max:100',
            'default_discount_absorption_percentage' => 'required|numeric|min:0|max:100',
        ]);
        PayoutSetting::create($data);
        return back()->with('success', 'Defaults updated.');
    }
}
