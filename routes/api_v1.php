<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['namespace' => "\App\Http\Controllers\Api\V1"], function () {

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get("protected_test", "Controller@protected_test");

        Route::post("logout", "AuthController@logout");
        Route::post("verify_email", "AuthController@verify_email")->middleware(['ability:verify-email']);
        Route::post("verify_forget_password", "AuthController@verify_forget_password")->middleware(['ability:forget-password']);;
        Route::post("reset_password", "AuthController@reset_password")->middleware(['ability:reset-password']);;
    });
    Route::post("test_login", "Controller@test_login");
    Route::get("test", "Controller@test");

    // Auth
    Route::post("register", "AuthController@register");
    Route::post("email_login", "AuthController@email_login");
    Route::post("google_login", "AuthController@google_login");
    Route::post("forget_password", "AuthController@forget_password");

    Route::get("email_template", function () {
        return new \App\Mail\EmailVerification(["code" => 59658]);
    });
});
