<?php

namespace App\Mail\Competition;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompetitionWon extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($competition, $prize_money)
    {
        $this->data['competition'] = $competition;
        $this->data['prize_money'] = $prize_money;
        $this->data['title'] = "Competition Won";
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("You won! #" . $this->data['competition']->slug)->markdown('emails.competition.won')->with('data', $this->data);
    }
}
