<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pickup List - {{ $batch->batch_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .page { page-break-after: always; padding: 20px; }
        .page:last-child { page-break-after: avoid; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #333; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p { font-size: 11px; color: #666; margin-top: 4px; }
        .vendor-info { background: #f8f8f8; padding: 12px; border-radius: 6px; margin-bottom: 15px; }
        .vendor-info h2 { font-size: 16px; margin-bottom: 6px; }
        .vendor-info .contact { font-size: 11px; color: #555; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background: #333; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .items-table td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 12px; }
        .items-table tr:nth-child(even) { background: #fafafa; }
        .checkbox-col { width: 30px; text-align: center; }
        .checkbox { width: 16px; height: 16px; border: 2px solid #333; border-radius: 3px; display: inline-block; }
        .signature-line { margin-top: 30px; padding-top: 15px; border-top: 1px dashed #ccc; display: flex; justify-content: space-between; }
        .signature-line .sig { border-bottom: 1px solid #333; width: 200px; padding-bottom: 3px; font-size: 10px; color: #888; }
        .summary { margin-top: 20px; padding: 12px; background: #f0f0f0; border-radius: 6px; }
        .summary h3 { font-size: 14px; margin-bottom: 8px; }
        .customer-section { margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .customer-section h4 { font-size: 13px; margin-bottom: 5px; }
        .customer-section .address { font-size: 10px; color: #666; margin-bottom: 8px; }
        @media print {
            body { font-size: 11px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    {{-- Print Button --}}
    <div class="no-print" style="padding: 15px; text-align: center; background: #f0f0f0; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #333; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
            🖨️ Print This Page
        </button>
        <a href="{{ route('shipments.show', $batch->id) }}" style="margin-left: 10px; padding: 10px 30px; background: #666; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; text-decoration: none;">
            ← Back
        </a>
    </div>

    {{-- SECTION 1: Vendor Pickup Sheets --}}
    @foreach($vendorGroups as $vendorId => $items)
        @php $vendor = $items->first()->vendor; @endphp
        <div class="page">
            <div class="header">
                <h1>📋 VENDOR PICKUP LIST</h1>
                <p>{{ $batch->batch_number }} • {{ now()->format('M d, Y') }} • Admin: {{ $batch->admin->name ?? 'N/A' }}</p>
            </div>

            <div class="vendor-info">
                <h2>{{ $vendor->store_name_in_arabic ?? $vendor->store_name_in_german ?? 'Vendor' }}</h2>
                <div class="contact">
                    @if($vendor->phone)📞 {{ $vendor->phone }} &nbsp;&nbsp;@endif
                    @if($vendor->address)📍 {{ $vendor->address }}@endif
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="checkbox-col">✓</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Qty</th>
                        <th>Order #</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $batchItem)
                        @php $oi = $batchItem->orderItem; @endphp
                        <tr>
                            <td class="checkbox-col"><span class="checkbox"></span></td>
                            <td style="font-weight: 500;">{{ $oi->product->name_arabic ?? 'Product' }}</td>
                            <td>
                                @if($oi->productVariant && $oi->productVariant->variantValues)
                                    @foreach($oi->productVariant->variantValues as $val)
                                        {{ $val->variantAttribute->name_arabic ?? '' }}: {{ $val->value }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td style="font-weight: bold;">{{ $oi->quantity }}</td>
                            <td style="font-family: monospace; font-size: 10px;">{{ $batchItem->order->order_number }}</td>
                            <td>{{ $batchItem->order->user->name ?? 'Customer' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align: right; font-size: 11px; color: #666;">
                Total items: <strong>{{ $items->count() }}</strong>
            </div>

            <div class="signature-line">
                <div>
                    <div class="sig">Collected by (Admin)</div>
                </div>
                <div>
                    <div class="sig">Vendor Confirmation</div>
                </div>
                <div>
                    <div class="sig">Date & Time</div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- SECTION 2: Customer Packing Slips --}}
    @foreach($customerGroups as $orderId => $items)
        @php
            $order = $items->first()->order;
            $address = $order->address;
        @endphp
        <div class="page">
            <div class="header">
                <h1>📦 PACKING SLIP</h1>
                <p>{{ $batch->batch_number }} • {{ $order->order_number }}</p>
            </div>

            <div class="customer-section">
                <h4>Ship To: {{ $order->user->name ?? 'Customer' }}</h4>
                <div class="address">
                    @if($address)
                        {{ $address->address }}<br>
                        @if($address->city){{ $address->city->name ?? '' }}@endif
                        @if($address->country), {{ $address->country->name ?? '' }}@endif<br>
                        @if($address->phone_number)📞 {{ $address->phone_number }}@endif
                    @endif
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="checkbox-col">✓</th>
                        <th>Product</th>
                        <th>From Vendor</th>
                        <th>Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $batchItem)
                        @php $oi = $batchItem->orderItem; @endphp
                        <tr style="{{ $batchItem->collection_status === 'unavailable' ? 'text-decoration: line-through; color: #999;' : '' }}">
                            <td class="checkbox-col"><span class="checkbox"></span></td>
                            <td>
                                {{ $oi->product->name_arabic ?? 'Product' }}
                                @if($oi->productVariant && $oi->productVariant->variantValues)
                                    <br><small style="color: #888;">
                                        @foreach($oi->productVariant->variantValues as $val)
                                            {{ $val->value }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </small>
                                @endif
                            </td>
                            <td>{{ $batchItem->vendor->store_name_in_arabic ?? $batchItem->vendor->store_name_in_german ?? '' }}</td>
                            <td style="font-weight: bold;">{{ $oi->quantity }}</td>
                            <td>
                                @if($batchItem->collection_status === 'unavailable')
                                    <span style="color: red;">✗ Unavailable</span>
                                @else
                                    ○
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 10px; font-size: 11px;">
                <strong>Order Total: IQD{{ number_format($order->final_price, 2) }}</strong>
            </div>
        </div>
    @endforeach
</body>
</html>
