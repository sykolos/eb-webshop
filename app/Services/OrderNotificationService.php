<?php

namespace App\Services;

use App\Jobs\SendOrderNotifications;
use App\Jobs\SendOrderStatusNotification;
use App\Models\Order;
use App\Models\OrderStatusHistory;

class OrderNotificationService
{
    public function dispatch(Order $order): void
    {
        SendOrderNotifications::dispatch($order->getKey())->afterCommit();
    }

    public function dispatchStatusChanged(Order $order, OrderStatusHistory $history): void
    {
        SendOrderStatusNotification::dispatch($order->getKey(), $history->getKey())->afterCommit();
    }
}
