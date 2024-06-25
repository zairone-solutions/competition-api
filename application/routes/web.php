<?php

use App\Events\NewMessageNotification;
use Illuminate\Support\Facades\Route;
use BeyondCode\LaravelWebSockets\Facades\WebSocketRouter;
use Ably\AblyRest;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\LedgersController;
use App\Models\Competition;


// Define a route to handle WebSocket connections

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    return response()->json([], 200)->header('Access-Control-Allow-Origin', '*');
});

Route::get('/socket.io', function (\Illuminate\Http\Request $request) {
    return response()->json([], 200)->header('Access-Control-Allow-Origin', '*');
});
Route::get('/message', function (\Illuminate\Http\Request $request) {
    try {
        $ably = new AblyRest(config('broadcasting.connections.ably.key'));
        $channel = $ably->channels->get('messages');

        $channel->publish('new-message', ['message' => "This is a message no." . rand(1111, 4444)]);

        return response()->json(['success' => true]);
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
});
WebSocketRouter::get('/socket', \App\Http\Controllers\WebSocketController::class);

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', function () {
    $competition = Competition::findOrFail(1);
    dd($competition->posts()->withMaxVotes($competition)->get()->toArray());
});
Route::get('/test-sockets', function () {
    return view('test-sockets');
});
Route::redirect('admin', 'login');
Route::redirect('/home', 'setting');

Auth::routes();

Auth::routes();

// Route::get('/home', 'App\Http\Controllers\HomeController@index')->name('home');

Route::group(['middleware' => 'auth'], function () {

    Route::resource('user', 'App\Http\Controllers\UserController', ['except' => ['show']]);
    Route::get('profile', ['as' => 'profile.edit', 'uses' => 'App\Http\Controllers\ProfileController@edit']);
    Route::put('profile', ['as' => 'profile.update', 'uses' => 'App\Http\Controllers\ProfileController@update']);

    Route::get('setting', ['as' => 'setting.edit', 'uses' => 'App\Http\Controllers\SettingController@edit']);
    Route::put('setting', ['as' => 'setting.update', 'uses' => 'App\Http\Controllers\SettingController@update']);

    // For Users Module
    Route::get('all-users', [UserController::class,'showuser'])->name('allusers');
    Route::get('add-user', [UserController::class,'adduser'])->name('adduser');
    Route::post('store-user', [UserController::class,'storeuser'])->name('storeuser');
    Route::get('edit-user/{id}', [UserController::class,'edituser'])->name('edituser');
    Route::post('update-user/{id}', [UserController::class,'updateuser'])->name('updateuser');
    Route::get('delete-user/{id}', [UserController::class,'deleteuser'])->name('deleteuser');

    // For Categories Module
    Route::get('all-categories', [CategoriesController::class,'showcategory'])->name('allcategories');
    Route::get('add-category', [CategoriesController::class,'addcategory'])->name('addcategory');
    Route::post('store-category', [CategoriesController::class,'storecategory'])->name('storecategory');
    Route::get('edit-category/{id}', [CategoriesController::class,'editcategory'])->name('editcategory');
    Route::post('update-category/{id}', [CategoriesController::class,'updatecategory'])->name('updatecategory');
    Route::get('delete-category/{id}', [CategoriesController::class,'deletecategory'])->name('deletecategory');

    // For Payment Methods Module
    Route::get('all-payment-methods', [PaymentMethodsController::class,'showpaymentmethods'])->name('allpaymentmethods');
    Route::get('add-payment-method', [PaymentMethodsController::class,'addpaymentmethod'])->name('addpaymentmethod');
    Route::post('store-payment-method', [PaymentMethodsController::class,'storepaymentmethod'])->name('storepaymentmethod');
    Route::get('edit-payment-method/{id}', [PaymentMethodsController::class,'editpaymentmethod'])->name('editpaymentmethod');
    Route::post('update-payment-method/{id}', [PaymentMethodsController::class,'updatepaymentmethod'])->name('updatepaymentmethod');
    Route::post('/update-payment-method-status/{id}', [PaymentMethodsController::class, 'updateStatus'])->name('updatepaymentmethodstatus');
    Route::get('delete-payment-method/{id}', [PaymentMethodsController::class,'deletepaymentmethod'])->name('deletepaymentmethod');

    // For Ledgers Module
    Route::get('all-ledgers', [LedgersController::class,'showledgers'])->name('allledgers');


    Route::get('upgrade', function () {
        return view('pages.upgrade');
    })->name('upgrade');
    Route::get('map', function () {
        return view('pages.maps');
    })->name('map');
    Route::get('icons', function () {
        return view('pages.icons');
    })->name('icons');
    Route::get('table-list', function () {
        return view('pages.tables');
    })->name('table');
    Route::put('profile/password', ['as' => 'profile.password', 'uses' => 'App\Http\Controllers\ProfileController@password']);
});
