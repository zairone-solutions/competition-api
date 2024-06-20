<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AuthUserResource;
use App\Http\Resources\NotificationResource;
use App\Mail\Auth\EmailVerification;
use App\Mail\Auth\ForgetPassword;
use App\Mail\Auth\ResetPasswordSuccess;
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
        try {
            $rules = [
                'email' => "required|email|unique:users",
                'full_name' => "required|min:3|max:25",
                'password' => "required|min:8|max:20",
            ];
            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            $data = $request->only(['email', 'password', 'full_name']);
            $username = $this->generateUserName($data['email']);

            $email_code = rand(11111, 99999);
            $user = User::create(['username' => $username, 'full_name' => $data['full_name'], 'email' => $data['email'], 'email_verification_code' => $email_code, 'password' => Hash::make($data['password'])]);

            $token = $user->createToken('email_verification_email', $request->get("deviceModel") ?? NULL, ["verify-email"]);

            @Mail::to($user)->send(new EmailVerification(['code' => $email_code]));

            $user->update(["email_verification_code_at" => date_format(new DateTime(), 'Y-m-d H:i:s')]);

            return $this->resData(['success' => "Registration successful!", "access_token" => $token->plainTextToken, 'token_type' => 'Bearer']);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function verify_email(Request $request)
    {
        try {
            $rules = ['code' => "required|numeric|digits:5"];
            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            if (auth()->user()->email_verification_code == $request->code) {
                auth()->user()->update(['email_verified_at' => date_format(new DateTime(), 'Y-m-d H:i:s')]);
                // auth()->user()->tokens()->delete();
                $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL, [auth()->user()->type]);

                return $this->resData(["user" => AuthUserResource::make(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
            } else {
                return $this->resMsg(['error' => "Invalid verification code!"], 'authentication', 400);
            }
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function verify_token(Request $request)
    {
        try {
            return $this->resData(["user" => AuthUserResource::make(auth()->user())]);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function resend_verification_email(Request $request)
    {
        try {
            $last_emailed_at = strtotime(auth()->user()->email_verification_code_at);
            $current_time_difference = time() - $last_emailed_at;

            if ($current_time_difference > 60) {
                $email_code = rand(11111, 99999);

                @Mail::to(auth()->user())->send(new EmailVerification(['code' => $email_code]));

                auth()->user()->update(["email_verification_code" => $email_code, "email_verification_code_at" => date_format(new DateTime(), 'Y-m-d H:i:s')]);

                return $this->resData(['success' => "Verification code sent again!"]);
            }
            $diff = 60 - $current_time_difference;
            return $this->resMsg(['error' => "Please wait for $diff seconds and try again."], 'validation', 400);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function forget_password(Request $request)
    {
        try {
            $rules = ['identity' => ["required", new IdentityRule()]];
            $errors = $this->reqValidate($request->all(), $rules, ['identity.required' => "Email or username is required."]);
            if ($errors)
                return $errors;

            if (filter_var($request->identity, FILTER_VALIDATE_EMAIL)) {
                $credentials['email'] = trim($request->identity);
            } else {
                $credentials['username'] = trim($request->identity);
            }
            if (!($user = User::email()->where($credentials)->first())) {
                return $this->resMsg(['identity' => ["This account is registered with Google."]], 'authentication', 400);
            }
            if ($user = User::where($credentials)->first()) {
                $password_reset = $user->password_resets()->create(['code' => rand(11111, 99999)]);
                $token = $user->createToken('forget_password', $request->get("deviceModel") ?? NULL, ["forget-password"]);
                @Mail::to($user)->send(new ForgetPassword(['code' => $password_reset->code]));

                return $this->resData(['success' => "An email has been sent to " . $user->email . ".", 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
            } else {
                return $this->resMsg(['identity' => ["No user found with the provided credentials."]], 'authentication', 400);
            }
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function resend_forget_password(Request $request)
    {
        try {
            $user = auth()->user();
            $password_reset = $user->password_resets()->create(['code' => rand(11111, 99999)]);

            $token = $user->createToken('forget_password', $request->get("deviceModel") ?? NULL, ["forget-password"]);
            @Mail::to($user)->send(new ForgetPassword(['code' => $password_reset->code]));

            return $this->resData(['success' => "An email has been sent to " . $user->email . ".", 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function verify_forget_password(Request $request)
    {
        try {
            $rules = ['code' => "required|numeric|digits:5"];
            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            if (auth()->user()->password_resets()->where(['code' => $request->code])->first()) {

                $token = auth()->user()->createToken('reset_password', $request->get("deviceModel") ?? NULL, ["reset-password"]);

                return $this->resData(['error' => "You can reset your password.", 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
            } else {
                return $this->resMsg(['error' => "Invalid verification code!"], 'authentication', 400);
            }
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function reset_password(Request $request)
    {
        try {
            $rules = ['password' => "required|min:8|max:20"];
            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            auth()->user()->update(['password' => Hash::make($request->password)]);
            auth()->user()->tokens()->delete();
            $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL, [auth()->user()->type]);

            // Email
            @Mail::to(auth()->user())->send(new ResetPasswordSuccess());

            return $this->resData(["user" => AuthUserResource::make(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function email_login(Request $request)
    {
        try {
            $rules = ['identity' => ["required", new IdentityRule()], 'password' => "required|min:8|max:20"];
            $errors = $this->reqValidate($request->all(), $rules, ['identity.required' => "Email or username is required."]);
            if ($errors)
                return $errors;

            if (filter_var($request->identity, FILTER_VALIDATE_EMAIL)) {
                $credentials['email'] = trim($request->identity);
            } else {
                $credentials['username'] = trim($request->identity);
            }
            if (User::where(array_merge($credentials, ["auth_provider" => "google"]))->count()) {
                return $this->resMsg(['error' => "Try logging in with Google account."], 'authentication', 400);
            }
            $credentials['password'] = $request->password;
            if (!Auth::once($credentials)) {
                return $this->resMsg(['error' => "Invalid login credentials!"], 'authentication', 400);
            }
            if (!auth()->user()->email_verified_at) {

                $last_emailed_at = strtotime(auth()->user()->email_verification_code_at);
                $current_time_difference = time() - $last_emailed_at;

                $token = auth()->user()->createToken('email_verification_email', $request->get("deviceModel") ?? NULL, ["verify-email"]);

                if ($current_time_difference > 60) {
                    $email_code = rand(11111, 99999);
                    auth()->user()->update(['email_verification_code' => $email_code, "email_verification_code_at" => date_format(new DateTime(), 'Y-m-d H:i:s')]);

                    @Mail::to(auth()->user())->send(new EmailVerification(['code' => $email_code]));
                }
                return $this->resMsg(['error' => "Please verify you email to continue.", 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer'], 'verification', 400);
            }
            auth()->user()->tokens()->delete();
            $token = auth()->user()->createToken('auth_token', $request->get("deviceModel") ?? NULL, [auth()->user()->type]);

            return $this->resData(["user" => AuthUserResource::make(auth()->user()), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function google_login(Request $request)
    {
        try {
            $rules = ['accessToken' => "required"];
            $errors = $this->reqValidate($request->all(), $rules, ['accessToken.required' => "Google OAuth token is missing."]);
            if ($errors)
                return $errors;

            $providerUser = Socialite::driver("google")->stateless()->userFromToken($request->accessToken);
            $user = User::where(['email' => $providerUser->email])->first();
            if (!$user) {
                $username = $this->generateUserName($providerUser->email);
                $user = User::create(['username' => $username, 'full_name' => $providerUser->name, 'email' => $providerUser->email, 'avatar' => $providerUser->avatar, 'auth_provider' => "google"]);
            }
            if (!$user->avatar)
                $user->update(['avatar' => $providerUser->avatar]);

            Auth::login($user);

            // auth()->user()->tokens()->delete();
            $token = $user->createToken('auth_token', $request->get("deviceModel") ?? NULL, [$user->type]);

            return $this->resData(["user" => AuthUserResource::make($user), 'access_token' => $token->plainTextToken, 'token_type' => 'Bearer']);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function logout()
    {
        try {
            if (auth()->check()) {
                auth()->user()->currentAccessToken()->delete();
                auth()->user()->update(['notification_token' => NULL]);
                return $this->resMsg(['success' => "User logout successfully"]);
            }
            return $this->resMsg(['error' => "Unauthorized!"], "authentication", 401);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function set_notification_token(Request $request)
    {
        try {
            $rules = ['notification_token' => "required"];
            $errors = $this->reqValidate($request->all(), $rules, ['notification_token.required' => "Notification token is missing."]);
            if ($errors)
                return $errors;

            auth()->user()->update(['notification_token' => $request->notification_token]);
            return $this->resMsg(['success' => "Notification token set successfully!"]);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function notifications(Request $request)
    {
        try {
            return $this->resData(NotificationResource::collection(auth()->user()->notifications()->get()));
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
}
