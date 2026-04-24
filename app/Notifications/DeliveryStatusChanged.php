<?php

namespace App\Notifications;

use App\Models\Delivery\DeliveryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DeliveryOrder $deliveryOrder,
        public string $previousStatus,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->deliveryOrder->status->label();
        $trackingUrl = route('delivery.track', ['token' => $this->deliveryOrder->share_token ?? '']);

        return (new MailMessage)
            ->subject("Delivery Status Update: {$this->deliveryOrder->delivery_number}")
            ->greeting("Hello {$this->deliveryOrder->recipient_name}!")
            ->line("Your delivery status has been updated to: {$statusLabel}")
            ->line("Delivery Number: {$this->deliveryOrder->delivery_number}")
            ->when($this->deliveryOrder->tracking_number, function ($mail) {
                return $mail->line("Tracking Number: {$this->deliveryOrder->tracking_number}");
            })
            ->when($this->deliveryOrder->courier, function ($mail) {
                return $mail->line("Courier: {$this->deliveryOrder->courier}");
            })
            ->when($this->deliveryOrder->delivery_date, function ($mail) {
                return $mail->line("Expected Delivery: {$this->deliveryOrder->delivery_date->format('M d, Y')}");
            })
            ->action('Track Delivery', $trackingUrl)
            ->line('Thank you for your business!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_order_id' => $this->deliveryOrder->id,
            'delivery_number' => $this->deliveryOrder->delivery_number,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'status_label' => $this->deliveryOrder->status->label(),
            'tracking_number' => $this->deliveryOrder->tracking_number,
            'courier' => $this->deliveryOrder->courier,
        ];
    }
}
