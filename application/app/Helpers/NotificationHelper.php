<?php

namespace App\Helpers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Codeliter\ExpoPush\Expo\PushNotification;

class NotificationHelper
{
    public static function send($user_id, $token, $title, $for = "", $description = "", $data = array(), $push = "push")
    {
        $notification = Notification::create(["user_id" => $user_id, "title" => $title, "description" => $description, "for" => $for, "data" => json_encode($data)]);

        RealTimeHelper::sendMessage("notifications", 'user-' . $user_id, ['notification' => NotificationResource::make($notification)]);

        if ($token && $push === "push") {
            PushNotification::send([$token], $title, $description);
        }

    }

}
