<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPayment extends Mailable
{
    use Queueable, SerializesModels;

    private $files;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->view('mails.orders.payment')->subject("Електронний поліс");

        foreach ($this->files as $file) {
            $filePath = storage_path('app/public/policies') . DIRECTORY_SEPARATOR . $file;

            $mail->attach($filePath);
        }

        return $mail;
    }
}
