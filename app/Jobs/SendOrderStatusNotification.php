<?php

namespace App\Jobs;

use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use App\Models\OrderNotificationDelivery;
use App\Models\OrderStatusHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];

    public function __construct(public int $orderId, public int $historyId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $order = Order::with('user')->findOrFail($this->orderId);
        $history = OrderStatusHistory::findOrFail($this->historyId);
        $delivery = OrderNotificationDelivery::firstOrCreate([
            'order_id' => $order->id,
            'notification_type' => 'status:'.$history->id,
        ]);

        if ($delivery->status === 'sent') {
            return;
        }

        $delivery->increment('attempts');

        try {
            Mail::to($order->email)->send(new OrderStatusChangedMail($order, $history));
            $delivery->update(['status' => 'sent', 'sent_at' => now(), 'last_error' => null]);
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
            throw $exception;
        }
    }
}
