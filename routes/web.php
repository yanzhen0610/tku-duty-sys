<?php

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
    return view('index');
})->name('home');

Route::resource('users', 'UsersController')->only(['index', 'show', 'store', 'update']);
Route::delete('users/{user}/password', 'UsersController@resetPassword')->name('users.password.reset');
Route::resource('groups', 'GroupsController')->only(['store', 'update', 'destroy']);

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);
Route::group([], function() {
    Route::get('password/reset', 'Auth\ForgotPasswordController@showRequestResetForm')->name('password.requestResetForm');
    Route::post('password/reset', 'Auth\ForgotPasswordController@requestReset')->name('password.requestReset');
});
