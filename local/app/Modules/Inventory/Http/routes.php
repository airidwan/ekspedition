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

Route::group(['prefix' => 'inventory'], function() {
    Route::group(['prefix' => 'master'], function() {
        Route::group(['prefix' => 'master-barang'], function() {
            Route::any('/', 'Master\MasterBarangController@index');
        });
        Route::group(['prefix' => 'master-uom'], function() {
            Route::any('/', 'Master\MasterUomController@index');
            Route::get('add', 'Master\MasterUomController@add');
            Route::get('edit/{id}', 'Master\MasterUomController@edit');
            Route::post('save', 'Master\MasterUomController@save');
            Route::post('delete', 'Master\MasterUomController@delete');
        });
        Route::group(['prefix' => 'master-category'], function() {
            Route::any('/', 'Master\MasterCategoryController@index');
            Route::get('add', 'Master\MasterCategoryController@add');
            Route::get('edit/{id}', 'Master\MasterCategoryController@edit');
            Route::post('save', 'Master\MasterCategoryController@save');
            Route::post('delete', 'Master\MasterCategoryController@delete');
        });
        Route::group(['prefix' => 'master-item'], function() {
            Route::any('/', 'Master\MasterItemController@index');
            Route::get('add', 'Master\MasterItemController@add');
            Route::get('edit/{id}', 'Master\MasterItemController@edit');
            Route::post('save', 'Master\MasterItemController@save');
            Route::post('delete', 'Master\MasterItemController@delete');
            Route::get('print-excel-index', 'Master\MasterItemController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-warehouse'], function() {
            Route::any('/', 'Master\MasterWarehouseController@index');
            Route::get('add', 'Master\MasterWarehouseController@add');
            Route::get('edit/{id}', 'Master\MasterWarehouseController@edit');
            Route::post('save', 'Master\MasterWarehouseController@save');
            Route::post('delete', 'Master\MasterWarehouseController@delete');
        });
    });
    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'stock-item'], function() {
            Route::any('/', 'Master\MasterStockController@index');
            Route::get('print-pdf-index', 'Master\MasterStockController@printPdfIndex');
            Route::get('print-excel-index', 'Master\MasterStockController@printExcelIndex');
        });
        Route::group(['prefix' => 'move-order'], function() {
            Route::any('/', 'Transaction\MoveOrderController@index');
            Route::get('add', 'Transaction\MoveOrderController@add');
            Route::get('edit/{id}', 'Transaction\MoveOrderController@edit');
            Route::post('save', 'Transaction\MoveOrderController@save');
            Route::post('cancel-mo', 'Transaction\MoveOrderController@cancelMo');
            Route::get('print-pdf/{id}', 'Transaction\MoveOrderController@printPdfDetail');

        });
        Route::group(['prefix' => 'adjustment-stock'], function() {
            Route::any('/', 'Transaction\AdjustmentStockController@index');
            Route::get('add', 'Transaction\AdjustmentStockController@add');
            Route::get('edit/{id}', 'Transaction\AdjustmentStockController@edit');
            Route::post('save', 'Transaction\AdjustmentStockController@save');
            Route::post('cancel-adj', 'Transaction\AdjustmentStockController@cancelAdj');
        });
        Route::group(['prefix' => 'warehouse-transfer'], function() {
            Route::any('/', 'Transaction\WarehouseTransferController@index');
            Route::get('add', 'Transaction\WarehouseTransferController@add');
            Route::get('edit/{id}', 'Transaction\WarehouseTransferController@edit');
            Route::post('save', 'Transaction\WarehouseTransferController@save');
            Route::post('cancel-wt', 'Transaction\WarehouseTransferController@cancelWt');
        });
        Route::group(['prefix' => 'branch-transfer'], function() {
            Route::any('/', 'Transaction\BranchTransferController@index');
            Route::get('add', 'Transaction\BranchTransferController@add');
            Route::get('edit/{id}', 'Transaction\BranchTransferController@edit');
            Route::post('save', 'Transaction\BranchTransferController@save');
            Route::post('cancel-bt', 'Transaction\BranchTransferController@cancelBt');
            Route::post('close', 'Transaction\BranchTransferController@close');
            Route::get('print-pdf/{id}', 'Transaction\BranchTransferController@printPdfDetail');
        });
        Route::group(['prefix' => 'receipt-po'], function() {
            Route::any('/', 'Transaction\ReceiptPurchaseOrderController@index');
            Route::get('add', 'Transaction\ReceiptPurchaseOrderController@add');
            Route::get('edit/{id}', 'Transaction\ReceiptPurchaseOrderController@edit');
            Route::post('save', 'Transaction\ReceiptPurchaseOrderController@save');
        });
        Route::group(['prefix' => 'return-po'], function() {
            Route::any('/', 'Transaction\ReturnPurchaseOrderController@index');
            Route::get('add', 'Transaction\ReturnPurchaseOrderController@add');
            Route::get('edit/{id}', 'Transaction\ReturnPurchaseOrderController@edit');
            Route::post('save', 'Transaction\ReturnPurchaseOrderController@save');
        });
        Route::group(['prefix' => 'receipt-branch-transfer'], function() {
            Route::any('/', 'Transaction\ReceiptBranchTransferController@index');
            Route::get('add', 'Transaction\ReceiptBranchTransferController@add');
            Route::get('edit/{id}', 'Transaction\ReceiptBranchTransferController@edit');
            Route::post('save', 'Transaction\ReceiptBranchTransferController@save');
        });
    });
    Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'move-order-all-branch'], function() {
            Route::any('/', 'Report\MoveOrderAllBranchController@index');
            Route::get('edit/{id}', 'Report\MoveOrderAllBranchController@edit');
            Route::get('print-pdf/{id}', 'Report\MoveOrderAllBranchController@printPdfDetail');
        });
    });
});
