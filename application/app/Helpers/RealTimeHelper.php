<?php

namespace App\Helpers;

use Ably\AblyRest;

class RealTimeHelper
{
    public static function sendMessage(string $channel, string $event, array $data)
    {
        $ably = new AblyRest(config('broadcasting.connections.ably.key'));

        $channel = $ably->channels->get($channel);
        $channel->publish($event, $data);
    }
}
