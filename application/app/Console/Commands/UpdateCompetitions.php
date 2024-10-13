<?php

namespace App\Console\Commands;

use App\Models\Competition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateCompetitions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:competitions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update competitions';

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
        Log::info("Updating competitions...");

        $competitions = Competition::readyForVoting()->get();
        foreach ($competitions as $competition) {
            $competition->update(['state' => "voting_period"]);
        }

        Log::info("Updated competitions.");
        return 0;
    }
}
