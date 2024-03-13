<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Mail\Competition\CompetitionParticipation;
use App\Mail\Competition\CompetitionParticipationAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CompetitionParticipatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $competition;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $competition)
    {
        $this->user = $user;
        $this->competition = $competition;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        @Mail::to($this->user)->send(new CompetitionParticipation($this->competition));
        @Mail::to($this->competition->organizer)->send(new CompetitionParticipationAlert($this->competition));

        NotificationHelper::send(
            $this->competition->organizer->id,
            $this->competition->organizer->notification_token,
            "New participation!",
            'participation',
            $this->user->username . " has participated in your competition.",
            ['id' => $this->competition->id, 'slug' => $this->competition->slug]
        );
    }
}
