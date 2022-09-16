<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AuthUserResource;
use App\Mail\EmailVerification;
use App\Mail\ForgetPassword;
use App\Mail\ResetPasswordSuccess;
use App\Models\User;
use App\Rules\IdentityRule;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Mail;

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
        $username = $this->generateUserName($data['email']);

        $email_code = rand(11111, 99999);
        $user = User::create(['username' => $username, 'full_name' => $data['full_name'], 'email' => $data['email'], 'email_verification_code' => $email_code, 'password' => Hash::make($data['password'])]);

        $token = $user->createToken('email_verification_email', $request->get("deviceModel") ?? NULL, ["verify-email"]);

        @Mail::to($user)->send(new EmailVerification(['code' => $email_code]));

        return $this->resData(['success' => "Registration successfull!", "access_token" => $token->plainTextToken, 'token_type' => 'Bearer']);
    }

    public function verify_email(Request $request)
    {
        $rules = ['code' => "required|numeric|digits:5"];
        $errors = $this->reqValidate($request->all(), $rules);
        if ($errors) return $errors;

        if (auth()->user()->email_verification_code == $request->code) {
            auth()->user()->update(['email_verified_at' => date_format(new DateTime(), 'Y-m-d H:i:s')]);
            // auth()->user()->tokens()->delete();
            $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL, [auth()->user()->type]);

            return $this->resData(["user" => AuthUserResource::make(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
        } else {
            return $this->resMsg(['error' => "Invalid verification code!"], 'authentication', 400);
        }
    }
    public function forget_password(Request $request)
    {
        $rules = ['identity' => ["required", new IdentityRule()]];
        $errors = $this->reqValidate($request->all(), $rules, ['identity.required' => "Email or username is required."]);
        if ($errors) return $errors;

        if (filter_var($request->identity, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = trim($request->identity);
        } else {
            $credentials['username'] = trim($request->identity);
        }

        if ($user = User::where($credentials)->first()) {
            $password_reset = $user->password_resets()->create(['code' => rand(11111, 99999)]);
            $token = $user->createToken('forget_password', $request->get("deviceModel") ?? NULL, ["forget-password"]);
            @Mail::to($user)->send(new ForgetPassword(['code' => $password_reset->code]));

            return $this->resData(['error' => "An email has been sent to " . $user->email . ".", 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
        } else {
            return $this->resMsg(['error' => "No user found with the provided credentials."], 'authentication', 400);
        }
    }

    public function verify_forget_password(Request $request)
    {
        $rules = ['code' => "required|numeric|digits:5"];
        $errors = $this->reqValidate($request->all(), $rules);
        if ($errors) return $errors;

        if (auth()->user()->password_resets()->where(['code' => $request->code])->first()) {

            $token = auth()->user()->createToken('reset_password', $request->get("deviceModel") ?? NULL, ["reset-password"]);

            return $this->resData(['error' => "You can reset your password.", 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
        } else {
            return $this->resMsg(['error' => "Invalid verification code!"], 'authentication', 400);
        }
    }

    public function reset_password(Request $request)
    {
        $rules = ['password' => "required|min:8|max:20"];
        $errors = $this->reqValidate($request->all(), $rules);
        if ($errors) return $errors;

        auth()->user()->update(['password' => Hash::make($request->password)]);
        auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL, [auth()->user()->type]);
        @Mail::to(auth()->user())->send(new ResetPasswordSuccess());

        return $this->resData(["user" => AuthUserResource::make(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
    }

    public function email_login(Request $request)
    {
        $rules = ['identity' => ["required", new IdentityRule()], 'password' => "required|min:8|max:20"];
        $errors = $this->reqValidate($request->all(), $rules, ['identity.required' => "Email or username is required."]);
        if ($errors) return $errors;

        if (filter_var($request->identity, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = trim($request->identity);
        } else {
            $credentials['username'] = trim($request->identity);
        }
        $credentials['password'] = $request->password;

        if (!Auth::once($credentials)) {
            return $this->resMsg(['error' => "Invalid login credentials!"], 'authentication', 400);
        }
        if (!auth()->user()->email_verified_at) {
            $email_code = rand(11111, 99999);
            auth()->user()->update(['email_verification_code' => $email_code]);

            $token = auth()->user()->createToken('email_verification_email', $request->get("deviceModel") ?? NULL, ["verify-email"]);

            @Mail::to(auth()->user())->send(new EmailVerification(['code' => $email_code]));

            return $this->resMsg(['error' => "Please verify you email to continue."], 'verification', 400);
        }
        // auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL, [auth()->user()->type]);

        return $this->resData(["user" => AuthUserResource::make(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
    }
    public function google_login(Request $request)
    {
        $rules = ['accessToken' => "required"];
        $errors = $this->reqValidate($request->all(), $rules, ['accessToken.required' => "Google OAuth token is missing."]);
        if ($errors) return $errors;

        $providerUser = Socialite::driver("google")->stateless()->userFromToken($request->accessToken);
        $user = User::where(['email' => $providerUser->email])->first();
        if (!$user) {
            $username = $this->generateUserName($providerUser->email);
            $user = User::create(['username' => $username, 'full_name' => $providerUser->name, 'email' => $providerUser->email, 'avatar' => $providerUser->avatar, 'auth_provider' => "google"]);
        }
        if (!$user->avatar) $user->update(['avatar' => $providerUser->avatar]);

        Auth::login($user);

        // auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL, [$user->type]);

        return $this->resData(["user" => AuthUserResource::make(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
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
