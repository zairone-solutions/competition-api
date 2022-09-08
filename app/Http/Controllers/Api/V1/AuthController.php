<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Rules\IdentityRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

class AuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function register(Request $request)
    {
        $rules = [
            'email' => "required|email|unique:users",
            'full_name' => "required|min:3|max:25",
            'password' => "required|min:8|max:20",
        ];
        $errors = $this->reqValidate($request->all(), $rules);
        if ($errors) return $errors;

        $data = $request->only(['email', 'password', 'full_name']);
        $username = strstr($data['email'], '@', true);

        User::create(['username' => $username, 'full_name' => $data['full_name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);

        return $this->resMsg(['success' => "Registration successfull!"]);
    }
    public function email_login(Request $request)
    {
        $rules = ['identity' => ["required", new IdentityRule()], 'password' => "required",];
        $errors = $this->reqValidate($request->all(), $rules, ['identity.required' => "Email or username is required."]);
        if ($errors) return $errors;

        if (filter_var($request->identity, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = trim($request->identity);
        } else {
            $credentials['username'] = trim($request->identity);
        }
        $credentials['password'] = $request->password;

        if (!Auth::once($credentials)) {
            return $this->resMsg(['message' => "Invalid login credentials!"], 'authentication', 401);
        }

        // auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL);


        return $this->resData(["user" => $this->userDisplay(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
    }
    public function google_login(Request $request)
    {
        $rules = ['accessToken' => "required"];
        $errors = $this->reqValidate($request->all(), $rules, ['accessToken.required' => "Google OAuth token is missing."]);
        if ($errors) return $errors;

        $providerUser = Socialite::driver("google")->stateless()->userFromToken($request->accessToken);
        $user = User::where(['email' => $providerUser->email])->first();
        if (!$user) {
            $username = strstr($providerUser->email, '@', true);
            $user = User::create(['username' => $username, 'full_name' => $providerUser->name, 'email' => $providerUser->email, 'avatar' => $providerUser->avatar, 'auth_provider' => "google"]);
        }
        if (!$user->avatar) $user->update(['avatar' => $providerUser->avatar]);

        Auth::login($user);

        // auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL);

        return $this->resData(["user" => $this->userDisplay(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
    }
    public function logout()
    {
        if (auth()->check()) {
            auth()->user()->currentAccessToken()->delete();
            return $this->resMsg(['success' => "User logout successfully"]);
        }
        return $this->resMsg(['error' => "Unauthorized!"], "authentication", 401);
    }
}
