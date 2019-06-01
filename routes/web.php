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

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);
Route::group([], function()
{
    Route::get('password/request_reset', 'Auth\ForgotPasswordController@showRequestResetForm')
        ->name('password.requestResetForm');
    Route::post('password/request_reset', 'Auth\ForgotPasswordController@requestReset')
        ->name('password.requestReset');
});

Route::resource('users', 'UsersController')
    ->only(['index', 'show', 'store', 'update']);
Route::delete('users/{user}/password', 'UsersController@resetPassword')
    ->name('users.password.reset');
Route::resource('groups', 'GroupsController')
    ->only(['index', 'store', 'update', 'destroy']);

Route::resource('shifts', 'ShiftsController')
    ->only(['index', 'show', 'store', 'update', 'destroy']);
Route::resource('areas', 'AreasController')
    ->only(['index', 'show', 'store', 'update', 'destroy']);

Route::resource('shifts_arrangements', 'ShiftsArrangementsController')
    ->only(['index', 'show', 'store', 'update', 'destroy']);

Route::group(['as' => 'pages.', 'middleware' => 'auth'], function()
{
    Route::get('users_and_groups', 'PagesController@usersAndGroups')
        ->name('users_and_groups');
    Route::get('areas_and_shifts', 'PagesController@areasAndShifts')
        ->name('areas_and_shifts');
    Route::get('shifts_arrangements_table', 'PagesController@shiftsArrangementsTable')
        ->name('shifts_arrangements_table');
});
