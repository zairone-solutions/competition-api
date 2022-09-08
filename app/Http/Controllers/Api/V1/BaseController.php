<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{

    public function resData($data, $error_type = FALSE, $code = 200)
    {
        return response()->json(['error_type' => $error_type, 'data' => $data], $code);
    }
    public function resMsg($data, $error_type = FALSE, $code = 200)
    {
        return response()->json(['error_type' => $error_type, 'messages' => $data], $code);
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
            return $this->resMsg($validator->errors(), 'validation', 401);
        }
    }
}
