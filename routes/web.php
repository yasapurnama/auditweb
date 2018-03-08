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

Route::get('/dashboard', 'HomeController@index')->name('dashboard');

Route::get('/scan', [
    'middleware' => ['auth'],
    'uses' => 'AuditController@index'
])->name('scan');
Route::post('/scan', [
    'middleware' => ['auth'],
    'uses' => 'AuditController@scan'
])->name('scan');
Route::get('/history', [
    'middleware' => ['auth'],
    'uses' => 'AuditController@history'
])->name('history');
Route::get('/history/{result}', [
    'middleware' => ['auth'],
    'uses' => 'AuditController@show'
])->name('result');
Route::post('/history/delete', [
    'middleware' => ['auth'],
    'uses' => 'AuditController@destroy'
])->name('result.delete');

Route::get('/download/{result}', 'DownloadController@download')->name('download');

Route::get('/profile', 'ProfileController@index')->name('profile');
Route::get('/profile/edit', 'ProfileController@edit')->name('editprofile');
Route::post('/profile/edit', 'ProfileController@update')->name('editprofile');

Route::get('/notification', 'NotificationController@index')->name('notification');
Route::get('/notification/update', 'NotificationController@update')->name('notification.update');

Route::get('/setting', 'SettingController@index')->name('setting');
Route::post('/setting', 'SettingController@update')->name('setting');

Route::group(['middleware' => 'admin'], function() {
    Route::get('/admin', 'AdminController@index')->name('admin');
});

// Route::get('user/{user}', [
//      'middleware' => ['auth', 'roles'],
//      'uses' => 'UserController@index',
//      'roles' => ['administrator', 'manager']
// ]);