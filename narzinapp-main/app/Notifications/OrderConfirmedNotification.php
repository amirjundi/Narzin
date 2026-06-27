<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Trait\PushNotification;

class OrderConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable, PushNotification;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // Add FCM push inside via if needed, or handle it manually
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Order Confirmed - ' . $this->order->order_number)
                    ->line('Your order ' . $this->order->order_number . ' has been successfully confirmed.')
                    ->line('Total amount: ' . number_format($this->order->final_price, 2) . ' IQD')
                    ->action('View Order', url('/api/v1/orders/' . $this->order->id))
                    ->line('Thank you for shopping with Narzin!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Confirmed',
            'body' => 'Your order ' . $this->order->order_number . ' has been confirmed.',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->order_status,
        ];
    }
}
