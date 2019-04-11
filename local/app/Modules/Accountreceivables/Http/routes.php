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

Route::group(['prefix' => 'accountreceivables'], function() {
    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'invoice'], function() {
            Route::any('/', 'Transaction\InvoiceController@index');
            Route::get('add-invoice-extra-cost', 'Transaction\InvoiceController@addInvoiceExtraCost');
            Route::get('edit/{id}', 'Transaction\InvoiceController@edit');
            Route::get('print-pdf/{id}', 'Transaction\InvoiceController@printPdf');
            Route::post('save', 'Transaction\InvoiceController@save');
            Route::post('save-add-invoice-extra-cost', 'Transaction\InvoiceController@saveAddInvoiceExtraCost');
            Route::get('print-excel-index', 'Transaction\InvoiceController@printExcelIndex');
            Route::get('get-json-customer', 'Transaction\InvoiceController@getJsonCustomer');
            Route::get('get-json-resi', 'Transaction\InvoiceController@getJsonResi');
            Route::get('get-json-coa', 'Transaction\InvoiceController@getJsonCoa');
            Route::post('cancel', 'Transaction\InvoiceController@cancel');
        });

        Route::group(['prefix' => 'batch-invoice'], function() {
            Route::any('/', 'Transaction\BatchInvoiceController@index');
            Route::get('add', 'Transaction\BatchInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\BatchInvoiceController@edit');
            Route::get('print-pdf/{id}', 'Transaction\BatchInvoiceController@printPdf');
            Route::post('save', 'Transaction\BatchInvoiceController@save');
            Route::post('cancel', 'Transaction\BatchInvoiceController@cancel');
            Route::get('get-json-customer', 'Transaction\BatchInvoiceController@getJsonCustomer');
            Route::get('get-json-invoice', 'Transaction\BatchInvoiceController@getJsonInvoice');
        });

        Route::group(['prefix' => 'approve-invoice'], function() {
            Route::any('/', 'Transaction\ApproveInvoiceController@index');
            Route::get('edit/{id}', 'Transaction\ApproveInvoiceController@edit');
            Route::post('save', 'Transaction\ApproveInvoiceController@save');
        });

        Route::group(['prefix' => 'approve-batch-invoice'], function() {
            Route::any('/', 'Transaction\ApproveBatchInvoiceController@index');
            Route::get('edit/{id}', 'Transaction\ApproveBatchInvoiceController@edit');
            Route::post('save', 'Transaction\ApproveBatchInvoiceController@save');
        });

        Route::group(['prefix' => 'cek-giro'], function() {
            Route::any('/', 'Transaction\CekGiroController@index');
            Route::get('add', 'Transaction\CekGiroController@add');
            Route::get('edit/{id}', 'Transaction\CekGiroController@edit');
            Route::post('save', 'Transaction\CekGiroController@save');
            Route::post('cancel', 'Transaction\CekGiroController@cancel');
            Route::get('get-json-customer', 'Transaction\CekGiroController@getJsonCustomer');
            Route::get('get-json-invoice', 'Transaction\CekGiroController@getJsonInvoice');
        });

        Route::group(['prefix' => 'receipt'], function() {
            Route::any('/', 'Transaction\ReceiptController@index');
            Route::get('add', 'Transaction\ReceiptController@add');
            Route::get('edit/{id}', 'Transaction\ReceiptController@edit');
            Route::post('save', 'Transaction\ReceiptController@save');
            Route::get('print-pdf/{id}', 'Transaction\ReceiptController@printPdf');
            Route::get('print-pdf-index', 'Transaction\ReceiptController@printPdfIndex');
            Route::get('print-excel-index', 'Transaction\ReceiptController@printExcelIndex');
            Route::get('get-json-invoice', 'Transaction\ReceiptController@getJsonInvoice');
            Route::get('get-json-batch-invoice', 'Transaction\ReceiptController@getJsonBatchInvoice');
            Route::get('get-json-bank', 'Transaction\ReceiptController@getJsonBank');
            Route::get('get-json-cek-giro', 'Transaction\ReceiptController@getJsonCekGiro');
        });

        Route::group(['prefix' => 'receipt-other'], function() {
            Route::any('/', 'Transaction\ReceiptOtherController@index');
            Route::get('add', 'Transaction\ReceiptOtherController@add');
            Route::get('edit/{id}', 'Transaction\ReceiptOtherController@edit');
            Route::get('print-pdf/{id}', 'Transaction\ReceiptOtherController@printPdf');
            Route::get('print-pdf-index', 'Transaction\ReceiptOtherController@printPdfIndex');
            Route::get('print-excel-index', 'Transaction\ReceiptOtherController@printExcelIndex');
            Route::post('save', 'Transaction\ReceiptOtherController@save');
            Route::get('get-json-resi', 'Transaction\ReceiptOtherController@getJsonResi');
            Route::get('get-json-invoice-ap', 'Transaction\ReceiptOtherController@getJsonInvoiceAp');
            Route::get('get-json-asset', 'Transaction\ReceiptOtherController@getJsonAsset');
            Route::get('get-json-bank', 'Transaction\ReceiptOtherController@getJsonBank');
            Route::get('get-json-cek-giro', 'Transaction\ReceiptOtherController@getJsonCekGiro');
            Route::get('get-json-coa', 'Transaction\ReceiptOtherController@getJsonCoa');
        });
    });
Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'cash-in'], function() {
            Route::any('/', 'Report\CashInController@index');
            Route::get('print-pdf-index', 'Report\CashInController@printPdfIndex');
            Route::get('print-excel-index', 'Report\CashInController@printExcelIndex');
        });
    });
});
