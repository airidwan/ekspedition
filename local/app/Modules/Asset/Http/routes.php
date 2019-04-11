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

Route::group(['prefix' => 'asset'], function() {
    Route::group(['prefix' => 'master'], function() {
        Route::group(['prefix' => 'asset-category'], function() {
            Route::any('/', 'Master\AssetCategoryController@index');
            Route::get('add', 'Master\AssetCategoryController@add');
            Route::get('edit/{id}', 'Master\AssetCategoryController@edit');
            Route::post('save', 'Master\AssetCategoryController@save');
            Route::post('delete', 'Master\AssetCategoryController@delete');
        });
    });
    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'mass-addition-asset'], function() {
            Route::any('/', 'Master\MasterAssetController@index');
            Route::get('add', 'Master\MasterAssetController@add');
            Route::get('edit/{id}', 'Master\MasterAssetController@edit');
            Route::post('save', 'Master\MasterAssetController@save');
            Route::post('delete', 'Master\MasterAssetController@delete');
        });
        Route::group(['prefix' => 'addition-asset'], function() {
            Route::any('/', 'Transaction\AdditionAssetController@index');
            Route::get('add', 'Transaction\AdditionAssetController@add');
            Route::get('edit/{id}', 'Transaction\AdditionAssetController@edit');
            Route::post('save', 'Transaction\AdditionAssetController@save');
            Route::post('delete', 'Transaction\AdditionAssetController@delete');
            Route::get('print-pdf-index', 'Transaction\AdditionAssetController@printPdfIndex');
            Route::get('print-excel-index', 'Transaction\AdditionAssetController@printExcelIndex');
        });
        Route::group(['prefix' => 'service-asset'], function() {
            Route::any('/', 'Transaction\ServiceAssetController@index');
            Route::get('add', 'Transaction\ServiceAssetController@add');
            Route::get('edit/{id}', 'Transaction\ServiceAssetController@edit');
            Route::post('save', 'Transaction\ServiceAssetController@save');
            Route::post('delete', 'Transaction\ServiceAssetController@delete');
            Route::get('print-excel-index', 'Transaction\ServiceAssetController@printExcelIndex');
        });
        Route::group(['prefix' => 'service-truck-monthly'], function() {
            Route::any('/', 'Transaction\ServiceTruckMonthlyController@index');
            Route::get('add', 'Transaction\ServiceTruckMonthlyController@add');
            Route::get('edit/{id}', 'Transaction\ServiceTruckMonthlyController@edit');
            Route::post('save', 'Transaction\ServiceTruckMonthlyController@save');
            Route::post('delete', 'Transaction\ServiceTruckMonthlyController@delete');
            Route::get('print-excel-index', 'Transaction\ServiceTruckMonthlyController@printExcelIndex');
        });
    });
    Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'asset-maintenance'], function() {
            Route::any('/', 'Report\AssetMaintenanceController@index');
            Route::get('print-pdf', 'Report\AssetMaintenanceController@printPdf');
            Route::get('print-excel', 'Report\AssetMaintenanceController@printExcel');
        });
        Route::group(['prefix' => 'truck-monthly-maintenance'], function() {
            Route::any('/', 'Report\TruckMonthlyMaintenanceController@index');
            Route::get('print-pdf', 'Report\TruckMonthlyMaintenanceController@printPdf');
            Route::get('print-excel', 'Report\TruckMonthlyMaintenanceController@printExcel');
        });
        Route::group(['prefix' => 'all-addition-asset'], function() {
            Route::any('/', 'Report\AllAdditionAssetController@index');
            Route::get('print-excel-index', 'Report\AllAdditionAssetController@printExcelIndex');
        });
    });
});
