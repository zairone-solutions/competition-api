<?php

namespace App\Helpers;

use App\Models\Competition;


class CompetitionHelper
{

    public static function getStage(Competition $competition)
    {
        if (!$competition->payment_verified_at) {
            return "payment_verification_pending";
        }
        if (strtotime($competition->voting_start_at) < time()) {
            return "participation_period";
        }
        if (strtotime($competition->voting_start_at) > time() && strtotime($competition->announcement_at) < time()) {
            return "voting_period";
        }
        if (strtotime($competition->announcement_at) > time()) {
            return "completed";
        }
    }
}
