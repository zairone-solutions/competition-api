<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
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
    Route::put('profile', ['as' => 'setting.update', 'uses' => 'App\Http\Controllers\SettingController@update']);


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
