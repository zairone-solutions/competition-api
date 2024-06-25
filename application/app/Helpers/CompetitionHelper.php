<?php

namespace App\Helpers;

use App\Models\Competition;


class CompetitionHelper
{

    public static function getStage(Competition $competition)
    {
        return $competition->state;
        // if (!$competition->payment_verified_at) {
        //     return "payment_verification_pending";
        // }
        // if (!$competition->isPublished()) {
        //     return "pending_publish";
        // }
        // if (strtotime($competition->voting_start_at) > time()) {
        //     return "participation_period";
        // }
        // if (strtotime($competition->voting_start_at) < time() && strtotime($competition->announcement_at) > time()) {
        //     return "voting_period";
        // }
        // if (strtotime($competition->announcement_at) < time()) {
        //     return "completed";
        // }
    }

    public static function extractUri(string $url)
    {

        $parsedUrl = parse_url($url);
        $uri = pathinfo($parsedUrl['path']);

        return substr($uri['dirname'] . "/" . $uri['basename'], 1);
    }
}
