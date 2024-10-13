<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use App\Mail\Competition\CompetitionWon;
use App\Models\Competition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AnnounceWinner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announce:winner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Announce competition winner';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info("Announcing Winners...");

        $competitions = Competition::readyForAnnouncement()->get();
        foreach ($competitions as $competition) {
            $competition->update(['state' => "completed"]);
            $this->decideWinners($competition);
        }

        Log::info("Announced Winners");
        return 0;
    }



    private function decideWinners(Competition $competition)
    {

        $postsWithMaxVotes = $competition->posts()->withMaxVotes($competition)->get();
        if (count($postsWithMaxVotes)) {

            $prizeMoney = (int) $competition->financial->prize_money / count($postsWithMaxVotes);

            foreach ($postsWithMaxVotes as $post) {

                $post->update(["won" => 1]);
                $competition->winners()->create(["winner_id" => $post->user_id]);

                $ledger = $post->user->ledgers()->create([
                    'title' => "#" . $competition->slug . " winning fee added to wallet",
                    'amount' => $prizeMoney,
                    'type' => 'credit',
                ]);

                @Mail::to($post->user)->send(new CompetitionWon($competition, $prizeMoney));
                NotificationHelper::send($post->user->id, $post->user->notification_token, "Competition Won!", 'won', "You have won #" . $competition->slug, ['id' => $competition->id], NULL);
            }
        }
    }
}
