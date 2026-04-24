<?php

namespace App\Listeners;

use App\Notifications\DeliveryStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendDeliveryStatusNotification implements ShouldQueue
{
    public function handle(object $event): void
    {
        // Check if this is a state transition event
        if (!isset($event->model) || !method_exists($event->model, 'getMorphClass')) {
            return;
        }

        $deliveryOrder = $event->model;

        // Only send notifications for delivery orders
        if ($deliveryOrder->getMorphClass() !== 'App\Models\Delivery\DeliveryOrder') {
            return;
        }

        // Get the previous and new status from the event
        $changes = $deliveryOrder->getChanges();
        if (!isset($changes['status'])) {
            return;
        }

        $previousStatus = $deliveryOrder->getOriginal('status');
        $newStatus = $changes['status'];

        // Send notification to customer via sales order
        if ($deliveryOrder->salesOrder && $deliveryOrder->salesOrder->customer) {
            $customer = $deliveryOrder->salesOrder->customer;
            
            // If customer has a user account, notify them
            if ($customer->user) {
                $customer->user->notify(new DeliveryStatusChanged(
                    $deliveryOrder,
                    $previousStatus,
                    $newStatus
                ));
            }
        }

        // Notify assigned user
        if ($deliveryOrder->user) {
            $deliveryOrder->user->notify(new DeliveryStatusChanged(
                $deliveryOrder,
                $previousStatus,
                $newStatus
            ));
        }
    }
}
