<?php

namespace App\Helpers;

use App\Models\Notification;
use Codeliter\ExpoPush\Expo\PushNotification;

class NotificationHelper
{
    public static function send($user_id, $token, $title, $for = "", $description = "", $data = array(), $push = "push")
    {
        Notification::create(["user_id" => $user_id, "title" => $title, "description" => $description, "for" => $for, "data" => json_encode($data)]);
        if ($token && $push == "push") PushNotification::send([$token], $title, $description);
    }

    public static function generateRandomString($length = 10)
    {
    }
}
