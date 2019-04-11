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

Route::group(['prefix' => 'payable'], function() {
    Route::group(['prefix' => 'master'], function() {
        Route::group(['prefix' => 'master-vendor'], function() {
            Route::any('', 'Master\MasterVendorController@index');
            Route::get('add', 'Master\MasterVendorController@add');
            Route::get('edit/{id}', 'Master\MasterVendorController@edit');
            Route::post('save', 'Master\MasterVendorController@save');
            Route::post('delete', 'Master\MasterVendorController@delete');
            Route::get('print-excel-index', 'Master\MasterVendorController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-ap-type'], function() {
            Route::any('', 'Master\MasterApTypeController@index');
            Route::get('edit/{id}', 'Master\MasterApTypeController@edit');
            Route::post('save', 'Master\MasterApTypeController@save');
            Route::post('delete', 'Master\MasterApTypeController@delete');
        });
    });

    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'po-invoice'], function() {
            Route::any('', 'Transaction\PurchaseOrderInvoiceController@index');
            Route::get('add', 'Transaction\PurchaseOrderInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\PurchaseOrderInvoiceController@edit');
            Route::post('save', 'Transaction\PurchaseOrderInvoiceController@save');
            Route::post('cancel', 'Transaction\PurchaseOrderInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\PurchaseOrderInvoiceController@printPdfDetail');
            Route::get('get-json-vendor', 'Transaction\PurchaseOrderInvoiceController@getJsonVendor');
            Route::get('get-json-po', 'Transaction\PurchaseOrderInvoiceController@getJsonPo');
            Route::get('print-excel-index', 'Transaction\PurchaseOrderInvoiceController@printExcelIndex');
        });
        Route::group(['prefix' => 'driver-salary-invoice'], function() {
            Route::any('', 'Transaction\DriverSalaryInvoiceController@index');
            Route::get('add', 'Transaction\DriverSalaryInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\DriverSalaryInvoiceController@edit');
            Route::post('save', 'Transaction\DriverSalaryInvoiceController@save');
            Route::post('cancel', 'Transaction\DriverSalaryInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\DriverSalaryInvoiceController@printPdfDetail');
            Route::get('get-json-driver', 'Transaction\DriverSalaryInvoiceController@getJsonDriver');
            Route::get('get-json-manifest', 'Transaction\DriverSalaryInvoiceController@getJsonManifest');
            Route::get('get-json-pickup', 'Transaction\DriverSalaryInvoiceController@getJsonPickup');
            Route::get('get-json-do', 'Transaction\DriverSalaryInvoiceController@getJsonDo');
        });
        Route::group(['prefix' => 'manifest-money-trip-invoice'], function() {
            Route::any('', 'Transaction\ManifestMoneyTripInvoiceController@index');
            Route::get('add', 'Transaction\ManifestMoneyTripInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\ManifestMoneyTripInvoiceController@edit');
            Route::post('save', 'Transaction\ManifestMoneyTripInvoiceController@save');
            Route::post('cancel', 'Transaction\ManifestMoneyTripInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\ManifestMoneyTripInvoiceController@printPdfDetail');
            Route::get('get-json-driver', 'Transaction\ManifestMoneyTripInvoiceController@getJsonDriver');
            Route::get('get-json-manifest', 'Transaction\ManifestMoneyTripInvoiceController@getJsonManifest');
        });
        Route::group(['prefix' => 'do-pickup-money-trip-invoice'], function() {
            Route::any('', 'Transaction\DoPickupMoneyTripInvoiceController@index');
            Route::get('add', 'Transaction\DoPickupMoneyTripInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\DoPickupMoneyTripInvoiceController@edit');
            Route::post('save', 'Transaction\DoPickupMoneyTripInvoiceController@save');
            Route::post('cancel', 'Transaction\DoPickupMoneyTripInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\DoPickupMoneyTripInvoiceController@printPdfDetail');
            Route::get('get-json-driver', 'Transaction\DoPickupMoneyTripInvoiceController@getJsonDriver');
            Route::get('get-json-do', 'Transaction\DoPickupMoneyTripInvoiceController@getJsonDo');
            Route::get('get-json-pickup', 'Transaction\DoPickupMoneyTripInvoiceController@getJsonPickup');
        });
        Route::group(['prefix' => 'do-partner-invoice'], function() {
            Route::any('', 'Transaction\DoPartnerInvoiceController@index');
            Route::get('add', 'Transaction\DoPartnerInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\DoPartnerInvoiceController@edit');
            Route::post('save', 'Transaction\DoPartnerInvoiceController@save');
            Route::post('cancel', 'Transaction\DoPartnerInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\DoPartnerInvoiceController@printPdfDetail');
            Route::get('get-json-do', 'Transaction\DoPartnerInvoiceController@getJsonDo');
        });
        Route::group(['prefix' => 'service-invoice'], function() {
            Route::any('', 'Transaction\ServiceInvoiceController@index');
            Route::get('add', 'Transaction\ServiceInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\ServiceInvoiceController@edit');
            Route::post('save', 'Transaction\ServiceInvoiceController@save');
            Route::post('cancel', 'Transaction\ServiceInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\ServiceInvoiceController@printPdfDetail');
            Route::get('get-json-vendor', 'Transaction\PurchaseOrderInvoiceController@getJsonVendor');
            Route::get('get-json-service', 'Transaction\ServiceInvoiceController@getJsonService');
        });
        Route::group(['prefix' => 'other-invoice'], function() {
            Route::any('', 'Transaction\OtherInvoiceController@index');
            Route::get('add', 'Transaction\OtherInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\OtherInvoiceController@edit');
            Route::post('save', 'Transaction\OtherInvoiceController@save');
            Route::post('cancel', 'Transaction\OtherInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\OtherInvoiceController@printPdfDetail');
            Route::get('get-json-driver', 'Transaction\OtherInvoiceController@getJsonDriver');
            Route::get('get-json-vendor', 'Transaction\OtherInvoiceController@getJsonVendor');
            Route::get('get-json-account', 'Transaction\OtherInvoiceController@getJsonAccount');
        });
        Route::group(['prefix' => 'approve-other-invoice'], function() {
            Route::any('', 'Transaction\ApproveOtherInvoiceController@index');
            Route::get('edit/{id}', 'Transaction\ApproveOtherInvoiceController@edit');
            Route::post('save', 'Transaction\ApproveOtherInvoiceController@save');
        });
        Route::group(['prefix' => 'ap-invoice'], function() {
            Route::any('', 'Transaction\ApInvoiceController@index');
            Route::get('add', 'Transaction\ApInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\ApInvoiceController@edit');
            Route::post('save', 'Transaction\ApInvoiceController@save');
            Route::post('cancel', 'Transaction\ApInvoiceController@cancel');
            Route::get('get-json-po/{vendor}', 'Transaction\ApInvoiceController@getJsonPo');
            Route::get('get-json-manifest/{driver}/{type}', 'Transaction\ApInvoiceController@getJsonManifest');
        });
        Route::group(['prefix' => 'dp-invoice'], function() {
            Route::any('', 'Transaction\DpInvoiceController@index');
            Route::get('add', 'Transaction\DpInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\DpInvoiceController@edit');
            Route::post('save', 'Transaction\DpInvoiceController@save');
            Route::post('cancel', 'Transaction\DpInvoiceController@cancel');
            Route::get('get-json-po/{vendor}', 'Transaction\DpInvoiceController@getJsonPo');
            Route::get('print-pdf-detail/{id}', 'Transaction\DpInvoiceController@printPdfDetail');
            Route::get('get-json-po-cicilan/{vendor}', 'Transaction\DpInvoiceController@getJsonPoCicilan');
            Route::get('get-json-manifest/{driver}/{type}', 'Transaction\DpInvoiceController@getJsonManifest');
        });
        Route::group(['prefix' => 'debt-employee-invoice'], function() {
            Route::any('', 'Transaction\DebtEmployeeInvoiceController@index');
            Route::get('add', 'Transaction\DebtEmployeeInvoiceController@add');
            Route::get('edit/{id}', 'Transaction\DebtEmployeeInvoiceController@edit');
            Route::post('save', 'Transaction\DebtEmployeeInvoiceController@save');
            Route::post('cancel', 'Transaction\DebtEmployeeInvoiceController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\DebtEmployeeInvoiceController@printPdfDetail');
        });
        Route::group(['prefix' => 'approve-debt-employee-invoice'], function() {
            Route::any('', 'Transaction\ApproveDebtEmployeeInvoiceController@index');
            Route::get('edit/{id}', 'Transaction\ApproveDebtEmployeeInvoiceController@edit');
            Route::post('save', 'Transaction\ApproveDebtEmployeeInvoiceController@save');
        });
        Route::group(['prefix' => 'payment'], function() {
            Route::any('', 'Transaction\PaymentController@index');
            Route::get('add/{id?}', 'Transaction\PaymentController@add');
            Route::get('edit/{id}', 'Transaction\PaymentController@edit');
            Route::post('save', 'Transaction\PaymentController@save');
            Route::post('cancel', 'Transaction\PaymentController@cancel');
            Route::get('print-pdf/{id}', 'Transaction\PaymentController@printPdf');
            Route::get('print-pdf-index', 'Transaction\PaymentController@printPdfIndex');
            Route::get('print-excel-index', 'Transaction\PaymentController@printExcelIndex');
            Route::get('get-json-bank', 'Transaction\PaymentController@getJsonBank');
            Route::get('get-json-invoice', 'Transaction\PaymentController@getJsonInvoice');
        });
        Route::group(['prefix' => 'inject-manifest-driver-and-assistant-salary'], function() {
            Route::any('', 'Transaction\InjectManifestDriverAndAssistantSalaryController@index');
            Route::get('edit/{id}', 'Transaction\InjectManifestDriverAndAssistantSalaryController@edit');
            Route::post('save', 'Transaction\InjectManifestDriverAndAssistantSalaryController@save');
        });
        Route::group(['prefix' => 'inject-do-driver-and-assistant-salary'], function() {
            Route::any('', 'Transaction\InjectDoDriverAndAssistantSalaryController@index');
            Route::get('edit/{id}', 'Transaction\InjectDoDriverAndAssistantSalaryController@edit');
            Route::post('save', 'Transaction\InjectDoDriverAndAssistantSalaryController@save');
        });
        Route::group(['prefix' => 'inject-pickup-driver-and-assistant-salary'], function() {
            Route::any('', 'Transaction\InjectPickupDriverAndAssistantSalaryController@index');
            Route::get('edit/{id}', 'Transaction\InjectPickupDriverAndAssistantSalaryController@edit');
            Route::post('save', 'Transaction\InjectPickupDriverAndAssistantSalaryController@save');
        });
    });
    Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'remaining-driver-kasbon'], function() {
            Route::any('', 'Report\RemainingDriverKasbonController@index');
            Route::get('print-excel', 'Report\RemainingDriverKasbonController@printExcel');
        });
        Route::group(['prefix' => 'remaining-employee-kasbon'], function() {
            Route::any('', 'Report\RemainingEmployeeKasbonController@index');
            Route::get('print-excel', 'Report\RemainingEmployeeKasbonController@printExcel');
        });
        Route::group(['prefix' => 'kasbon-history'], function() {
            Route::any('', 'Report\KasbonHistoryController@index');
            Route::get('print-excel', 'Report\KasbonHistoryController@printExcel');
        });
        Route::group(['prefix' => 'purchase-order'], function() {
            Route::any('', 'Report\PurchaseOrderController@index');
            Route::get('print-excel', 'Report\PurchaseOrderController@printExcel');
        });
        Route::group(['prefix' => 'purchase-order-credit'], function() {
            Route::any('', 'Report\PurchaseOrderCreditController@index');
            Route::get('print-excel', 'Report\PurchaseOrderCreditController@printExcel');
        });
        Route::group(['prefix' => 'cash-out'], function() {
            Route::any('', 'Report\CashOutController@index');
            Route::get('print-pdf-index', 'Report\CashOutController@printPdfIndex');
            Route::get('print-excel-index', 'Report\CashOutController@printExcelIndex');
        });
    });
});
