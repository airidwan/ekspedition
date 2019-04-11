<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::auth();
Route::any('/', 'HomeController@index');

Route::any('change-profile', 'HomeController@changeProfile');
Route::any('change-password', 'HomeController@changePassword');
Route::post('ganti-role-dan-cabang', 'HomeController@gantiRoleDanCabang');
Route::get('get-json-role-branch/{roleId}', 'HomeController@getJsonRoleBranch');
Route::get('update-data-dashboard', 'HomeController@updateDataDashboard');

Route::any('notification', 'NotificationController@index');
Route::get('read-notification/{id}', 'NotificationController@readNotification');
Route::get('get-notifications', 'NotificationController@getNotifications');

Route::group(['prefix' => 'locked'], function() {
    Route::get('', 'Auth\LockScreenController@index');
    Route::post('post', 'Auth\LockScreenController@post');
    Route::get('not-user', 'Auth\LockScreenController@notUser');
});

Route::group(['prefix' => 'sys-admin'], function() {
    Route::group(['prefix' => 'master'], function() {
        Route::group(['prefix' => 'user'], function() {
            Route::any('', 'Master\UserController@index');
            Route::get('add', 'Master\UserController@add');
            Route::get('edit/{id}', 'Master\UserController@edit');
            Route::post('save', 'Master\UserController@save');
            Route::post('delete', 'Master\UserController@delete');
            Route::get('print-excel', 'Master\UserController@printExcel');
        });
        Route::group(['prefix' => 'role'], function() {
            Route::any('', 'Master\RoleController@index');
            Route::get('add', 'Master\RoleController@add');
            Route::get('edit/{id}', 'Master\RoleController@edit');
            Route::post('save', 'Master\RoleController@save');
            Route::post('delete', 'Master\RoleController@delete');
        });
        Route::group(['prefix' => 'dummy'], function() {
            Route::any('', 'Master\DummyController@index');
            Route::get('add', 'Master\DummyController@add');
            Route::get('edit/{id}', 'Master\DummyController@edit');
            Route::post('save', 'Master\DummyController@save');
            Route::post('delete', 'Master\DummyController@delete');
            Route::any('get-header', 'Master\DummyController@getHeader');
        });
    });
});
