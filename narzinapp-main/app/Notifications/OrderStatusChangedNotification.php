<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Trait\PushNotification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable, PushNotification;

    protected $order;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $oldStatus, $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Order Update - ' . $this->order->order_number)
                    ->line('The status of your order ' . $this->order->order_number . ' has changed.')
                    ->line('New status: ' . ucfirst(str_replace('_', ' ', $this->newStatus)))
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
            'title' => 'Order Update',
            'body' => 'Your order ' . $this->order->order_number . ' status changed to ' . ucfirst(str_replace('_', ' ', $this->newStatus)),
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
