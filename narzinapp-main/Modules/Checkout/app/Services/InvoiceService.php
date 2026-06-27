<?php

namespace Modules\Checkout\Services;

use Modules\Checkout\Models\Order;

class InvoiceService
{
    /**
     * Generate an invoice for the given order
     */
    public function generateInvoice(Order $order)
    {
        // Ensure relationships are loaded
        $order->load(['user', 'address', 'items.product', 'items.productVariant', 'status']);
        
        $invoiceNumber = 'INV-' . $order->order_number;
        $date = $order->created_at->format('Y-m-d');
        
        // This is a stub for a PDF generator or HTML view
        // In a real scenario, this would return a PDF download response
        // using a package like barryvdh/laravel-dompdf
        
        $invoiceData = [
            'invoice_number' => $invoiceNumber,
            'date' => $date,
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
                'address' => $order->address ? $order->address->address : 'N/A',
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'product_name' => $item->product ? $item->product->name_arabic : 'Unknown',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'summary' => [
                'subtotal' => $order->total_amount,
                'discount' => $order->total_amount - $order->price_after_discount,
                'shipping' => $order->shipping_cost,
                'wallet_usage' => $order->wallet_usage,
                'total' => $order->final_price,
            ]
        ];

        return $invoiceData;
    }
}
