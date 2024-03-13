<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Notification;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Codeliter\ExpoPush\Expo\PushNotification;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    protected $API_VERSION = "v1";
    protected $API_URI = "api/v1/";

    public function resData($data, $error_type = FALSE, $code = 200)
    {
        return response()->json(['error_type' => $error_type, 'data' => $data], $code);
    }
    public function resMsg($data, $error_type = FALSE, $code = 200)
    {
        return response()->json(['error_type' => $error_type, 'messages' => $data], $code);
    }
    public function generateUserName($email)
    {
        $username = strstr($email, '@', true);
        if (User::where(['username' => $username])->first()) {
            return $username . rand(111, 999);
        }
        return $username;
    }
    public function userDisplay(User $user)
    {
        $avatar = $user->avatar ?? asset('storage/images/"user.png');
        return [
            "full_name" => $user->full_name, "email" => $user->email, "username" => $user->username, "avatar" => $avatar
        ];
    }
    public function reqValidate($data, $rules, $messages = [])
    {
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return $this->resMsg($validator->errors(), 'validation', 400);
        }
    }

    public function triggerNotification($user_id, $token, $title, $for = "", $description = "", $data = array(), $push = "push")
    {
        try {
            Notification::create(["user_id" => $user_id, "title" => $title, "description" => $description, "for" => $for, "data" => json_encode($data)]);
            if ($token && $push == "push") PushNotification::send([$token], $title, $description, "");
        } catch (\Throwable $th) {
        }
    }
    public function cName($competition_name)
    {
        return "#" . $competition_name;
    }
}
