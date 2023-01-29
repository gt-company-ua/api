<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPayment extends Mailable
{
    use Queueable, SerializesModels;

    private $filepath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.order-payment')->subject("Електронний поліс")->attach($this->filepath);
    }
}
