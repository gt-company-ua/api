<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    private $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $letter = $this->markdown('mails.orders.created', [
            'order' => $this->order
        ]);

        if ( ! is_null($this->order->files)) {
            foreach ($this->order->files as $file) {
                $exists = Storage::disk('public')->exists($file->path);
                if ($exists) {
                    $letter->attachFromStorageDisk('public', $file->path, $file->name);
                }
            }
        }

        return $letter->to(env('MAIL_OFFICE'))
            ->subject("Новый заказ №" . $this->order->id);
    }
}
