<?php

namespace App\Services;

use App\Jobs\SendOrderNotifications;
use App\Models\Order;

class OrderNotificationService
{
    public function dispatch(Order $order): void
    {
        SendOrderNotifications::dispatch($order->getKey())->afterCommit();
    }
}
