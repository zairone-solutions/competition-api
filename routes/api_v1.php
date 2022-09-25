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

        // Auth
        Route::post("logout", "AuthController@logout");
        Route::post("verify_email", "AuthController@verify_email")->middleware(['ability:verify-email']);
        Route::post("verify_forget_password", "AuthController@verify_forget_password")->middleware(['ability:forget-password']);
        Route::post("reset_password", "AuthController@reset_password")->middleware(['ability:reset-password']);
        Route::post("set_notification_token", "AuthController@set_notification_token");

        // Categories
        Route::post("categories", "CategoryController@request");
        Route::get("categories", "CategoryController@all");

        // Competitions
        Route::get("competitions", "CompetitionController@all");
        Route::post("competitions", "CompetitionController@store");
        Route::post("competitions/{competition}/publish", "CompetitionController@publish");
        Route::post("competitions/{competition}/participate", "CompetitionController@participate");
        Route::put("competitions/{competition}", "CompetitionController@update");
        // Competition Comments
        Route::get("competitions/{competition}/comments", "CompetitionController@comments_all");
        Route::get("competitions/{competition}/comments/{competition_comment}", "CompetitionController@comment_replies_all");
        Route::post("competitions/{competition}/comments", "CompetitionController@comments_store");
        Route::post("competitions/{competition}/comments/{competition_comment}", "CompetitionController@comment_replies");
        Route::put("competitions/{competition}/comments/{competition_comment}", "CompetitionController@comment_update");

        // Posts
        Route::get("posts", "PostController@personal");
        Route::get("posts/{competition}", "PostController@all");
        Route::post("posts/{competition}", "PostController@store")->middleware("competition_participant");
        Route::put("posts/{competition}/update/{post}", "PostController@update")->middleware("competition_participant");
        Route::delete("posts/{competition}/delete_image/{post_image}", "PostController@delete_image")->middleware("competition_participant");
        Route::post("posts/{competition}/upload_image/{post}", "PostController@upload_image")->middleware("competition_participant");

        Route::put("posts/{competition}/approve/{post}", "PostController@approve")->middleware("competition_organizer");
        Route::post("posts/{competition}/object/{post}", "PostController@object")->middleware("competition_organizer");
        Route::post("posts/{competition}/toggle_show/{post}", "PostController@toggle_show")->middleware("competition_organizer");

        Route::post("posts/{competition}/vote/{post}", "PostController@vote")->middleware("post_voter");
        Route::post("posts/{competition}/report/{post}", "PostController@report")->middleware("post_voter");

        // Organizer
        Route::get("organizer/reports", "OrganizerController@reports")->middleware(['ability:organizer']);
        Route::post("organizer/clear_report_toggle/{post_report}", "OrganizerController@clear_report_toggle")->middleware(['ability:organizer']);
    });
    Route::post("test_login", "Controller@test_login");
    Route::get("test", "Controller@test");

    // Auth
    Route::post("register", "AuthController@register");
    Route::post("email_login", "AuthController@email_login");
    Route::post("google_login", "AuthController@google_login");
    Route::post("forget_password", "AuthController@forget_password");

    Route::get("email_template", function () {
        return new \App\Mail\Competition\CompetitionPublished(\App\Models\Competition::find(1));
    });
});
