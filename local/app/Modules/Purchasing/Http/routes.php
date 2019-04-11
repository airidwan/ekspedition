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

Route::group(['prefix' => 'purchasing'], function() {
    Route::group(['prefix' => 'master'], function() {
        Route::group(['prefix' => 'master-type-po'], function() {
            Route::any('', 'Master\MasterTypePoController@index');
            Route::get('add', 'Master\MasterTypePoController@add');
            Route::get('edit/{id}', 'Master\MasterTypePoController@edit');
            Route::post('save', 'Master\MasterTypePoController@save');
            Route::post('delete', 'Master\MasterTypePoController@delete');
        });
    });
    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'purchase-order'], function() {
            Route::any('', 'Transaction\PurchaseOrderController@index');
            Route::get('add', 'Transaction\PurchaseOrderController@add');
            Route::get('edit/{id}', 'Transaction\PurchaseOrderController@edit');
            Route::post('save', 'Transaction\PurchaseOrderController@save');
            Route::post('delete', 'Transaction\PurchaseOrderController@delete');
            Route::post('cancel-po', 'Transaction\PurchaseOrderController@cancelPo');
            Route::get('print-pdf-detail/{id}', 'Transaction\PurchaseOrderController@printPdfDetail');
            Route::get('get-json-manifest', 'Transaction\PurchaseOrderController@getJsonManifest');
            Route::get('get-json-do', 'Transaction\PurchaseOrderController@getJsonDeliveryOrder');
            Route::get('get-json-pickup-form', 'Transaction\PurchaseOrderController@getJsonPickupForm');
        });
        Route::group(['prefix' => 'purchase-approve'], function() {
            Route::any('', 'Transaction\PurchaseApproveController@index');
            Route::get('edit/{id}', 'Transaction\PurchaseApproveController@edit');
            Route::post('save', 'Transaction\PurchaseApproveController@save');
        });
    });
    Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'purchase-order-outstanding'], function() {
            Route::any('', 'Report\PurchaseOrderOutstandingController@index');
        });
    });
});
