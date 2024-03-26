<?php

namespace App\Jobs\Competitions;

use App\Helpers\NotificationHelper;
use App\Mail\Competition\CompetitionPublished;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CompetitionPublishedNotification implements ShouldQueue
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
        @Mail::to($this->user)->send(new CompetitionPublished($this->competition));

        NotificationHelper::send(
            $this->competition->organizer->id,
            $this->competition->organizer->notification_token,
            "Competition is live",
            'competition_is_live',
            $this->competition->slug . " is live now! Share with friends and followers to get maximum participations.",
            ['id' => $this->competition->id, 'slug' => $this->competition->slug]
        );
    }
}
