<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public OrderStatusHistory $history)
    {
    }

    public function build()
    {
        return $this->view('emails.order-status-changed')
            ->subject('Rendelés állapota frissítve: '.$this->order->displayNumber());
    }
}
