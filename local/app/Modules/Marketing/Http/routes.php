<?php

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for the module.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::group(['prefix' => 'marketing'], function() {
    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'operator-book'], function() {
            Route::any('', 'Transaction\OperatorBookController@index');
            Route::get('add', 'Transaction\OperatorBookController@add');
            Route::get('edit/{id}', 'Transaction\OperatorBookController@edit');
            Route::post('save', 'Transaction\OperatorBookController@save');
            Route::get('print-excel', 'Transaction\OperatorBookController@printExcel');
        });
        Route::group(['prefix' => 'complain'], function() {
            Route::any('', 'Transaction\ComplainController@index');
            Route::get('add', 'Transaction\ComplainController@add');
            Route::get('edit/{id}', 'Transaction\ComplainController@edit');
            Route::post('save', 'Transaction\ComplainController@save');
            Route::get('get-json-resi', 'Transaction\ComplainController@getJsonResi');
            Route::get('print-excel', 'Transaction\ComplainController@printExcel');
        });
        Route::group(['prefix' => 'pickup-request'], function() {
            Route::any('', 'Transaction\PickupRequestController@index');
            Route::get('add', 'Transaction\PickupRequestController@add');
            Route::get('edit/{id}', 'Transaction\PickupRequestController@edit');
            Route::get('approve/{id}', 'Transaction\PickupRequestController@approve');
            Route::post('save', 'Transaction\PickupRequestController@save');
            Route::post('save-approve', 'Transaction\PickupRequestController@saveApprove');
            Route::post('cancel-pr', 'Transaction\PickupRequestController@cancelPr');
            Route::get('print-pdf-detail/{id}', 'Transaction\PickupRequestController@printPdfDetail');
            Route::get('print-excel', 'Transaction\PickupRequestController@printExcel');

        });
    });
});