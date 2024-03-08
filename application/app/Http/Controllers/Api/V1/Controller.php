<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    // method for user logout and delete token
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }

    public function test()
    {
        return response(['name' => "Ali Naqi Al-Musawi"]);
    }
    public function protected_test()
    {
        return response(auth()->user());
    }
    public function test_login(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        if (!$user) {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }
        Auth::login($user);
        $user->tokens()->delete();
        $token = $user->createToken('remember_token')->plainTextToken;

        return response()
            ->json(['message' => 'Hi ' . $user->name . ', welcome to home', 'access_token' => $token, 'token_type' => 'Bearer',]);
    }
}
