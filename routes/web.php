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
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth', 'user'])->group(function () {
    //[User]
    //Web Auditor
    Route::get('/scan', 'AuditController@index')->name('scan');
    Route::post('/scan', 'AuditController@scan')->name('scan');
    Route::get('/history', 'AuditController@history')->name('history');
    Route::get('/history/{result}', 'AuditController@show')->name('result');
    Route::post('/history/delete', 'AuditController@destroy')->name('result.delete');

    //Notification
    Route::get('/notification', 'NotificationController@index')->name('notification');
    Route::get('/notification/update', 'NotificationController@update')->name('notification.update');
    Route::post('/notification/delete', 'NotificationController@destroy')->name('notification.delete');

    //Setting
    Route::get('/setting', 'SettingController@index')->name('setting');
    Route::post('/setting', 'SettingController@update')->name('setting');


    //[User And Admin]
    //Dashboard
    Route::get('/dashboard', 'HomeController@index')->name('dashboard');

    //Profile
    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::get('/profile/edit', 'ProfileController@edit')->name('editprofile');
    Route::post('/profile/edit', 'ProfileController@update')->name('editprofile');

    //[Admin]
    Route::group(['prefix' => 'manage', 'middleware' => 'admin'], function() {
        //Audit Result
        Route::get('/history', 'HistoryController@index')->name('manage.history');
        Route::get('/history/{result}', 'HistoryController@show')->name('manage.result');
        Route::post('/history/delete', 'HistoryController@destroy')->name('manage.deleteresult');

        //Users
        Route::get('/users', 'UsersController@index')->name('manage.users');
        Route::get('/users/{user}', 'UsersController@show')->name('manage.userview');
        Route::get('/users/{user}/edit', 'UsersController@edit')->name('manage.useredit');
        Route::post('/users/{user}/edit', 'UsersController@update')->name('manage.useredit');
        Route::post('/users/ban', 'UsersController@disable')->name('manage.userban');
    });

});

Route::get('/download/{result}', 'DownloadController@download')->name('download');
