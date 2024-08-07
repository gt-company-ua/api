<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderOffer extends Mailable
{
    use Queueable, SerializesModels;

    private $files;
    private $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $files, $code)
    {
        $this->files = $files;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->view('mails.orders.offer', ['code' => $this->code])->subject("Персональна оферта");

        foreach ($this->files as $file) {
            $filePath = storage_path('app/public/policies') . DIRECTORY_SEPARATOR . $file;

            $mail->attach($filePath);
        }

        return $mail;
    }
}
