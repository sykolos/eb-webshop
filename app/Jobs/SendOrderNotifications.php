<?php

namespace App\Jobs;

use App\Mail\SuccesOrderAdminMail;
use App\Mail\SuccesOrderCustomerMail;
use App\Models\Order;
use App\Models\OrderNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public array $backoff = [30, 120, 600];

    public function __construct(public int $orderId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $order = Order::with([
            'user', 'items', 'items.product', 'items.product.product_unit',
            'user_shipping', 'user_invoice',
        ])->findOrFail($this->orderId);
        $states = ['függőben', 'feldolgozás', 'kiszállítva', 'törölve'];

        $this->sendOnce($order, 'customer', function () use ($order, $states) {
            Mail::to($order->user->email)
                ->cc('noreply@electrobusiness.hu')
                ->send(new SuccesOrderCustomerMail($order, $states, $order->id));
        });

        $this->sendOnce($order, 'admin', function () use ($order, $states) {
            Mail::to('info@electrobusiness.hu')
                ->send(new SuccesOrderAdminMail($order, $states, $order->id));
        });
    }

    private function sendOnce(Order $order, string $type, callable $send): void
    {
        $delivery = OrderNotificationDelivery::firstOrCreate([
            'order_id' => $order->id,
            'notification_type' => $type,
        ]);

        if ($delivery->status === 'sent') {
            return;
        }

        $delivery->increment('attempts');

        try {
            $send();
            $delivery->update([
                'status' => 'sent',
                'sent_at' => now(),
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $delivery->update([
                'status' => 'failed',
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);
            throw $exception;
        }
    }
}
