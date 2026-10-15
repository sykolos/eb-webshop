<?php

namespace App\Mail;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CartReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cart $cart, public User $user)
    {
    }

    public function build()
    {
        return $this->view('emails.cart-reminder')
            ->text('emails.cart-reminder-text')
            ->subject('A kosaradban még vannak termékek');
    }
}
