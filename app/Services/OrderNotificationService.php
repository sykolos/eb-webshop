<?php

namespace App\Services;

use App\Jobs\SendOrderNotifications;
use App\Jobs\SendOrderStatusNotification;
use App\Models\Order;
use App\Models\OrderStatusHistory;

class OrderNotificationService
{
    public function __construct(private readonly AutomationRuleService $automations)
    {
    }

    public function dispatch(Order $order): void
    {
        SendOrderNotifications::dispatch($order->getKey())->afterCommit();
    }

    public function dispatchStatusChanged(Order $order, OrderStatusHistory $history): void
    {
        if ($this->automations->isEnabled('order_status_changed')) {
            $order->loadMissing('user');
            if ($order->user) {
                $this->automations->dispatchEvent('order_status_changed', $order->user, [
                    'order_id' => $order->id,
                    'history_id' => $history->id,
                    'status' => $history->to_status,
                ]);
            }
            return;
        }
        SendOrderStatusNotification::dispatch($order->getKey(), $history->getKey())->afterCommit();
    }
}
