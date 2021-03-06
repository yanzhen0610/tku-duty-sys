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

Route::get('/', 'HomeController@index')->name('home');

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);
Route::group([], function()
{
    Route::get(
        'password/request_reset',
        'Auth\ForgotPasswordController@showRequestResetForm'
    )->name('password.requestResetForm');

    Route::post(
        'password/request_reset',
        'Auth\ForgotPasswordController@requestReset'
    )->name('password.requestReset');
});
Route::group(['as' => 'user.', 'middleware' => 'auth'], function()
{
    Route::get('user/self', 'UserProfile@self')->name('self');
    Route::post(
        'user/self/reset_password',
        'UserProfile@resetPassword'
    )->name('reset_password');
});

Route::group(['as' => 'admin.', 'middleware' => ['auth', 'admin']], function()
{
    Route::get(
        'admin/change_user_password/{user}',
        'AdministrationController@changeUserPasswordPage'
    )->name('changeUserPasswordPage');

    Route::post(
        'admin/change_user_password/{user}',
        'AdministrationController@changeUserPassword'
    )->name('changeUserPassword');
});

Route::group([], function()
{
    Route::resource('users', 'UsersController')
        ->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::delete(
        'users/{user}/password',
        'UsersController@resetPassword'
    )->name('users.password.reset');

    Route::resource('groups', 'GroupsController')
        ->only(['index', 'store', 'update', 'destroy']);
});

Route::resource('shifts', 'ShiftsController')
    ->only(['index', 'show', 'store', 'update', 'destroy']);
Route::resource('areas', 'AreasController')
    ->only(['index', 'show', 'store', 'update', 'destroy']);

Route::resource('shifts_arrangements', 'ShiftsArrangementsController')
    ->only(['index', 'show', 'store', 'destroy']);

Route::get(
    'download_shifts_arrangements_xlsx',
    'ShiftsArrangementsController@downloadShiftsArrangementsXlsx'
)->name('shifts_arrangements.download_xlsx');

Route::group(['as' => 'shift_arrangement_locks'], function()
{
    Route::get(
        'shift_arrangement_locks',
        'ShiftArrangementLocksController@index'
    )->name('.index');

    Route::post(
        'shift_arrangement_locks',
        'ShiftArrangementLocksController@update'
    )->name('.update');
});

Route::group(['as' => 'pages.'], function()
{
    Route::get(
        'users_table',
        'PagesController@users'
    )->name('users');

    Route::get(
        'areas_table',
        'PagesController@areas'
    )->name('areas');

    Route::get(
        'shifts_table',
        'PagesController@shifts'
    )->name('shifts');

    Route::get(
        'shifts_arrangements_table',
        'PagesController@shiftsArrangementsTable'
    )->name('shifts_arrangements_table');

    Route::get(
        'shifts_arrangements_changes',
        'PagesController@shiftsArrangementsChanges'
    )->name('shifts_arrangements_changes');
});
