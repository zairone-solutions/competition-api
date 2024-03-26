<?php

namespace App\Jobs\Competitions;

use App\Helpers\NotificationHelper;
use App\Mail\Competition\CompetitionPaymentSuccess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CompetitionPaymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    protected $competition;
    protected $payment;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $competition, $payment)
    {
        $this->user = $user;
        $this->competition = $competition;
        $this->payment = $payment;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        @Mail::to($this->user)->send(new CompetitionPaymentSuccess($this->competition, $this->payment));

        NotificationHelper::send(
            $this->competition->organizer->id,
            $this->competition->organizer->notification_token,
            "Payment Successful!",
            'competition_payment_success',
            $this->competition->slug . " has been paid. You can publish whenever you like.",
            ['id' => $this->competition->id, 'slug' => $this->competition->slug]
        );
    }
}
