<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssistMe extends Mailable
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
        $pdf = PDF::loadView('mails.orders.pdf-assist', [
            'number' => $this->order->assist->number,
            'name' => $this->order->insurant->fullname,
            'duration' => ($this->order->trip_duration == 0) ? '15 дн.' : $this->order->trip_duration . ' міс.',
            'price' => $this->order->assist->price,
            'date' => date('d.m.Y', strtotime($this->order->polis_start))
        ], [], 'UTF-8');
        $pdf->setOption([
            'defaultFont' => 'DejaVu Serif'
        ]);

        return $this->view('mails.orders.payment-assist', ['fullName' => $this->order->insurant->fullname])
            ->subject("Договір розширеного покриття - AssistMe від Greentravel")
            ->attachData($pdf->output(), 'AssistMe.pdf');
    }
}
