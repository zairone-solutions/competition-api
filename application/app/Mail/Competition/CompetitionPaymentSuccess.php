<?php

namespace App\Mail\Competition;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompetitionPaymentSuccess extends Mailable
{
    use Queueable, SerializesModels;
    private $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($competition, $payment)
    {
        $this->data['competition'] = $competition;
        $this->data['payment'] = $payment;
        $this->data["title"] = "Payment Successful";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Competition Payment Success!")->markdown('emails.competition.payment_success')->with('data', $this->data);
    }
}
