<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Trait\PushNotification;

class VendorOrderNotification extends Notification implements ShouldQueue
{
    use Queueable, PushNotification;

    protected $order;
    protected $items;

    /**
     * Create a new notification instance.
     *
     * @param $order The order that was placed
     * @param $items Collection of order items belonging to this vendor
     */
    public function __construct($order, $items)
    {
        $this->order = $order;
        $this->items = $items;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $itemCount = $this->items->sum('quantity');
        $itemNames = $this->items->map(function ($item) {
            return ($item->product->name_arabic ?? 'Product') . ' ×' . $item->quantity;
        })->implode(', ');

        return [
            'title' => 'New Order - Prepare Items!',
            'body' => "Order {$this->order->order_number}: {$itemCount} items need to be prepared for pickup.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'item_count' => $itemCount,
            'items' => $itemNames,
            'type' => 'vendor_order',
        ];
    }
}
