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

Route::any('tracking-resi', 'Report\TrackingResiWebController@tracking');
Route::any('calculate-price', 'Report\PriceListController@calculatePrice');

Route::group(['middleware' => ['apiToken']], function() {
    Route::post('get-tracking-resi', 'Report\TrackingResiWebController@getTracking');
    Route::post('get-city', 'Report\PriceListController@getCity');
    Route::post('get-calculate-price', 'Report\PriceListController@getCalculatePrice');
});
Route::group(['prefix' => 'operational'], function() {
    Route::group(['prefix' => 'master'], function() {
        Route::group(['prefix' => 'master-organization'], function() {
            Route::any('', 'Master\MasterOrganizationController@index');
            Route::post('save', 'Master\MasterOrganizationController@save');
        });
        Route::group(['prefix' => 'master-branch'], function() {
            Route::any('', 'Master\MasterBranchController@index');
            Route::get('add', 'Master\MasterBranchController@add');
            Route::get('edit/{id}', 'Master\MasterBranchController@edit');
            Route::post('save', 'Master\MasterBranchController@save');
            Route::post('delete', 'Master\MasterBranchController@delete');
            Route::get('print-excel', 'Master\MasterBranchController@printExcel');
        });
        Route::group(['prefix' => 'master-commodity'], function() {
            Route::any('', 'Master\MasterCommodityController@index');
            Route::get('add', 'Master\MasterCommodityController@add');
            Route::get('edit/{id}', 'Master\MasterCommodityController@edit');
            Route::post('save', 'Master\MasterCommodityController@save');
            Route::post('delete', 'Master\MasterCommodityController@delete');
            Route::get('print-excel', 'Master\MasterCommodityController@printExcel');
        });
        Route::group(['prefix' => 'master-region'], function() {
            Route::any('', 'Master\MasterRegionController@index');
            Route::get('add', 'Master\MasterRegionController@add');
            Route::get('edit/{id}', 'Master\MasterRegionController@edit');
            Route::post('save', 'Master\MasterRegionController@save');
            Route::post('delete', 'Master\MasterRegionController@delete');
            Route::get('print-excel', 'Master\MasterRegionController@printExcel');
        });
        Route::group(['prefix' => 'master-customer'], function() {
            Route::any('', 'Master\MasterCustomerController@index');
            Route::get('add', 'Master\MasterCustomerController@add');
            Route::get('edit/{id}', 'Master\MasterCustomerController@edit');
            Route::post('save', 'Master\MasterCustomerController@save');
            Route::post('delete', 'Master\MasterCustomerController@delete');
            Route::get('print-excel', 'Master\MasterCustomerController@printExcel');
        });
        Route::group(['prefix' => 'master-partner'], function() {
            Route::any('', 'Master\MasterPartnerController@index');
            Route::get('add', 'Master\MasterPartnerController@add');
            Route::get('edit/{id}', 'Master\MasterPartnerController@edit');
            Route::post('save', 'Master\MasterPartnerController@save');
            Route::post('delete', 'Master\MasterPartnerController@delete');
        });
        Route::group(['prefix' => 'master-driver'], function() {
            Route::any('', 'Master\MasterDriverController@index');
            Route::get('add', 'Master\MasterDriverController@add');
            Route::get('edit/{id}', 'Master\MasterDriverController@edit');
            Route::post('save', 'Master\MasterDriverController@save');
            Route::post('delete', 'Master\MasterDriverController@delete');
            Route::get('print-excel', 'Master\MasterDriverController@printExcel');
        });
        Route::group(['prefix' => 'master-truck-brand'], function() {
            Route::any('', 'Master\MasterTruckBrandController@index');
            Route::get('add', 'Master\MasterTruckBrandController@add');
            Route::get('edit/{id}', 'Master\MasterTruckBrandController@edit');
            Route::post('save', 'Master\MasterTruckBrandController@save');
            Route::post('delete', 'Master\MasterTruckBrandController@delete');
        });
        Route::group(['prefix' => 'master-truck-type'], function() {
            Route::any('', 'Master\MasterTruckTypeController@index');
            Route::get('add', 'Master\MasterTruckTypeController@add');
            Route::get('edit/{id}', 'Master\MasterTruckTypeController@edit');
            Route::post('save', 'Master\MasterTruckTypeController@save');
            Route::post('delete', 'Master\MasterTruckTypeController@delete');
        });
        Route::group(['prefix' => 'master-truck'], function() {
            Route::any('', 'Master\MasterTruckController@index');
            Route::get('add', 'Master\MasterTruckController@add');
            Route::get('edit/{id}', 'Master\MasterTruckController@edit');
            Route::post('save', 'Master\MasterTruckController@save');
            Route::post('delete', 'Master\MasterTruckController@delete');
            Route::get('print-excel', 'Master\MasterTruckController@printExcel');
        });
        Route::group(['prefix' => 'master-routes-rates'], function() {
            Route::any('', 'Master\MasterRoutesRatesController@index');
            Route::get('add', 'Master\MasterRoutesRatesController@add');
            Route::get('edit/{id}', 'Master\MasterRoutesRatesController@edit');
            Route::post('save', 'Master\MasterRoutesRatesController@save');
            Route::post('delete', 'Master\MasterRoutesRatesController@delete');
            Route::get('print-excel', 'Master\MasterRoutesRatesController@printExcel');
        });
        Route::group(['prefix' => 'master-money-trip'], function() {
            Route::any('', 'Master\MasterMoneyTripController@index');
            Route::get('add', 'Master\MasterMoneyTripController@add');
            Route::get('edit/{id}', 'Master\MasterMoneyTripController@edit');
            Route::post('save', 'Master\MasterMoneyTripController@save');
            Route::post('delete', 'Master\MasterMoneyTripController@delete');
            Route::get('print-excel-index', 'Master\MasterMoneyTripController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-delivery-area-money-trip'], function() {
            Route::any('', 'Master\MasterDeliveryAreaMoneyTripController@index');
            Route::get('add', 'Master\MasterDeliveryAreaMoneyTripController@add');
            Route::get('edit/{id}', 'Master\MasterDeliveryAreaMoneyTripController@edit');
            Route::post('save', 'Master\MasterDeliveryAreaMoneyTripController@save');
            Route::post('delete', 'Master\MasterDeliveryAreaMoneyTripController@delete');
            Route::get('print-excel-index', 'Master\MasterDeliveryAreaMoneyTripController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-delivery-area'], function() {
            Route::any('', 'Master\MasterDeliveryAreaController@index');
            Route::get('add', 'Master\MasterDeliveryAreaController@add');
            Route::get('edit/{id}', 'Master\MasterDeliveryAreaController@edit');
            Route::post('save', 'Master\MasterDeliveryAreaController@save');
            Route::post('delete', 'Master\MasterDeliveryAreaController@delete');
            Route::get('print-excel-index', 'Master\MasterDeliveryAreaController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-rent-car'], function() {
            Route::any('', 'Master\MasterRentCarController@index');
            Route::get('add', 'Master\MasterRentCarController@add');
            Route::get('edit/{id}', 'Master\MasterRentCarController@edit');
            Route::post('save', 'Master\MasterRentCarController@save');
            Route::post('delete', 'Master\MasterRentCarController@delete');
            Route::get('print-excel-index', 'Master\MasterRentCarController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-shipping-price'], function() {
            Route::any('', 'Master\MasterShippingPriceController@index');
            Route::get('add', 'Master\MasterShippingPriceController@add');
            Route::get('edit/{id}', 'Master\MasterShippingPriceController@edit');
            Route::post('save', 'Master\MasterShippingPriceController@save');
            Route::post('delete', 'Master\MasterShippingPriceController@delete');
            Route::get('get-json-commodity', 'Master\MasterShippingPriceController@getJsonCommodity');
            Route::get('print-excel-index', 'Master\MasterShippingPriceController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-driver-salary'], function() {
            Route::any('', 'Master\MasterDriverSalaryController@index');
            Route::get('add', 'Master\MasterDriverSalaryController@add');
            Route::get('edit/{id}', 'Master\MasterDriverSalaryController@edit');
            Route::post('save', 'Master\MasterDriverSalaryController@save');
            Route::post('delete', 'Master\MasterDriverSalaryController@delete');
            Route::get('print-excel-index', 'Master\MasterDriverSalaryController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-do-pickup-driver-salary'], function() {
            Route::any('', 'Master\MasterDoPickupDriverSalaryController@index');
            Route::get('add', 'Master\MasterDoPickupDriverSalaryController@add');
            Route::get('edit/{id}', 'Master\MasterDoPickupDriverSalaryController@edit');
            Route::post('save', 'Master\MasterDoPickupDriverSalaryController@save');
            Route::post('delete', 'Master\MasterDoPickupDriverSalaryController@delete');
            Route::get('print-excel-index', 'Master\MasterDoPickupDriverSalaryController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-alert-resi-stock'], function() {
            Route::any('', 'Master\MasterAlertResiStockController@index');
            Route::get('add', 'Master\MasterAlertResiStockController@add');
            Route::get('edit/{id}', 'Master\MasterAlertResiStockController@edit');
            Route::post('save', 'Master\MasterAlertResiStockController@save');
            Route::get('print-excel-index', 'Master\MasterAlertResiStockController@printExcelIndex');
        });
        Route::group(['prefix' => 'master-city'], function() {
            Route::any('', 'Master\MasterCityController@index');
            Route::get('add', 'Master\MasterCityController@add');
            Route::get('edit/{id}', 'Master\MasterCityController@edit');
            Route::post('save', 'Master\MasterCityController@save');
            Route::post('delete', 'Master\MasterCityController@delete');
            Route::get('print-excel-index', 'Master\MasterCityController@printExcelIndex');
        });
    });
    Route::group(['prefix' => 'transaction'], function() {
        Route::group(['prefix' => 'transaction-resi'], function() {
            Route::any('', 'Transaction\TransactionResiController@index');
            Route::get('add', 'Transaction\TransactionResiController@add');
            Route::get('edit/{id}', 'Transaction\TransactionResiController@edit');
            Route::get('print-excel-index', 'Transaction\TransactionResiController@printExcelIndex');
            Route::get('print-pdf/{id}', 'Transaction\TransactionResiController@printPdf');
            Route::get('print-pdf-tanpa-biaya/{id}', 'Transaction\TransactionResiController@printPdfTanpaBiaya');
            Route::get('print-voucher/{id}', 'Transaction\TransactionResiController@printVoucher');
            Route::post('save', 'Transaction\TransactionResiController@save');
            Route::get('get-json-item-unit-rute/{routeId}', 'Transaction\TransactionResiController@getJsonItemUnitRute');
            Route::get('get-json-route', 'Transaction\TransactionResiController@getJsonRoute');
        });
        Route::group(['prefix' => 'approve-nego-resi'], function() {
            Route::any('', 'Transaction\ApproveNegoResiController@index');
            Route::get('add', 'Transaction\ApproveNegoResiController@add');
            Route::get('edit/{id}', 'Transaction\ApproveNegoResiController@edit');
            Route::post('save', 'Transaction\ApproveNegoResiController@save');
        });
        Route::group(['prefix' => 'manifest'], function() {
            Route::any('', 'Transaction\ManifestController@index');
            Route::get('add', 'Transaction\ManifestController@add');
            Route::get('edit/{id}', 'Transaction\ManifestController@edit');
            Route::post('save', 'Transaction\ManifestController@save');
            Route::post('close', 'Transaction\ManifestController@close');
            Route::get('get-json-resi', 'Transaction\ManifestController@getJsonResi');
            Route::get('get-json-po', 'Transaction\ManifestController@getJsonPo');
            Route::post('get-driver-and-assistant-salary', 'Transaction\ManifestController@getDriverAndAssistantSalary');
            Route::get('print-pdf-index', 'Transaction\ManifestController@printPdfIndex');
            Route::get('print-excel-index', 'Transaction\ManifestController@printExcelIndex');
            Route::get('print-pdf-detail/{id}', 'Transaction\ManifestController@printPdfDetail');
            Route::get('print-pdf-detail-b/{id}', 'Transaction\ManifestController@printPdfDetailB');
            Route::get('print-pdf-report/{id}', 'Transaction\ManifestController@printPdfReport');
        });
        Route::group(['prefix' => 'money-trip-manifest'], function() {
            Route::any('', 'Transaction\MoneyTripManifestController@index');
            Route::get('edit/{id}', 'Transaction\MoneyTripManifestController@edit');
            Route::post('save', 'Transaction\MoneyTripManifestController@save');
            Route::post('cancel', 'Transaction\MoneyTripManifestController@cancel');
        });
        Route::group(['prefix' => 'approve-manifest'], function() {
            Route::any('', 'Transaction\ApproveManifestController@index');
            Route::get('edit/{id}', 'Transaction\ApproveManifestController@edit');
            Route::post('save', 'Transaction\ApproveManifestController@save');
            Route::post('cancel', 'Transaction\ApproveManifestController@cancel');
        });
        Route::group(['prefix' => 'shipment-manifest'], function() {
            Route::any('', 'Transaction\ShipmentManifestController@index');
            Route::get('edit/{id}', 'Transaction\ShipmentManifestController@edit');
            Route::post('save', 'Transaction\ShipmentManifestController@save');
            Route::post('cancel', 'Transaction\ShipmentManifestController@cancel');
        });
         Route::group(['prefix' => 'arrive-manifest'], function() {
            Route::any('', 'Transaction\ArriveManifestController@index');
            Route::get('edit/{id}', 'Transaction\ArriveManifestController@edit');
            Route::post('save', 'Transaction\ArriveManifestController@save');
            Route::post('cancel', 'Transaction\ArriveManifestController@cancel');
            Route::get('print-excel-checklist/{id}', 'Transaction\ArriveManifestController@printExcelChecklist');
            Route::get('print-pdf-checklist/{id}', 'Transaction\ArriveManifestController@printPdfChecklist');
        });
        Route::group(['prefix' => 'pickup-form'], function() {
            Route::get('add', 'Transaction\PickupFormController@add');
            Route::any('', 'Transaction\PickupFormController@index');
            Route::get('edit/{id}', 'Transaction\PickupFormController@edit');
            Route::post('save', 'Transaction\PickupFormController@save');
            Route::post('cancel', 'Transaction\PickupFormController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\PickupFormController@printPdfDetail');
        });
        Route::group(['prefix' => 'receipt-manifest'], function() {
            Route::get('add', 'Transaction\ReceiptManifestController@add');
            Route::any('', 'Transaction\ReceiptManifestController@index');
            Route::get('edit/{id}', 'Transaction\ReceiptManifestController@edit');
            Route::post('save', 'Transaction\ReceiptManifestController@save');
            Route::get('print-pdf-detail/{id}', 'Transaction\ReceiptManifestController@printPdfDetail');
        });
        Route::group(['prefix' => 'return-manifest'], function() {
            Route::get('add', 'Transaction\ReturnManifestController@add');
            Route::any('', 'Transaction\ReturnManifestController@index');
            Route::get('edit/{id}', 'Transaction\ReturnManifestController@edit');
            Route::post('save', 'Transaction\ReturnManifestController@save');
            Route::get('print-pdf-detail/{id}', 'Transaction\ReturnManifestController@printPdfDetail');
        });
        Route::group(['prefix' => 'stock-resi'], function() {
            Route::any('/', 'Transaction\StockResiController@index');
            Route::get('print-pdf-checklist', 'Transaction\StockResiController@printPdfChecklist');
            Route::get('print-excel-checklist', 'Transaction\StockResiController@printExcelChecklist');
            Route::get('print-pdf-report', 'Transaction\StockResiController@printPdfReport');
            Route::get('print-excel-report', 'Transaction\StockResiController@printExcelReport');
        });
        Route::group(['prefix' => 'wdl'], function() {
            Route::any('/', 'Transaction\ResiStockController@index');
            Route::get('edit/{id}', 'Transaction\ResiStockController@edit');
            Route::post('save', 'Transaction\ResiStockController@save');
            Route::get('print-pdf', 'Transaction\ResiStockController@printPdf');
            Route::get('print-excel', 'Transaction\ResiStockController@printExcel');
        });
        Route::group(['prefix' => 'draft-delivery-order'], function() {
            Route::get('add', 'Transaction\DraftDeliveryOrderController@add');
            Route::any('', 'Transaction\DraftDeliveryOrderController@index');
            Route::get('edit/{id}', 'Transaction\DraftDeliveryOrderController@edit');
            Route::post('save', 'Transaction\DraftDeliveryOrderController@save');
            Route::post('cancel-do', 'Transaction\DraftDeliveryOrderController@cancelDraftDo');
            Route::get('get-json-resi', 'Transaction\DraftDeliveryOrderController@getJsonResi');
            Route::get('print-pdf-detail/{id}', 'Transaction\DraftDeliveryOrderController@printPdfDetail');
        });
        Route::group(['prefix' => 'delivery-order'], function() {
            Route::get('add', 'Transaction\DeliveryOrderController@add');
            Route::any('', 'Transaction\DeliveryOrderController@index');
            Route::get('edit/{id}', 'Transaction\DeliveryOrderController@edit');
            Route::post('save', 'Transaction\DeliveryOrderController@save');
            Route::post('cancel-do', 'Transaction\DeliveryOrderController@cancelDo');
            Route::get('print-pdf-detail/{id}', 'Transaction\DeliveryOrderController@printPdfDetail');
        });
        Route::group(['prefix' => 'cost-delivery-order'], function() {
            Route::any('', 'Transaction\CostDeliveryOrderController@index');
            Route::get('edit/{id}', 'Transaction\CostDeliveryOrderController@edit');
            Route::post('save', 'Transaction\CostDeliveryOrderController@save');
        });
        Route::group(['prefix' => 'approve-delivery-order'], function() {
            Route::any('', 'Transaction\ApproveDeliveryOrderController@index');
            Route::get('edit/{id}', 'Transaction\ApproveDeliveryOrderController@edit');
            Route::post('save', 'Transaction\ApproveDeliveryOrderController@save');
        });
        Route::group(['prefix' => 'resi-stock-correction'], function() {
            Route::any('', 'Transaction\ResiStockCorrectionController@index');
            Route::get('add', 'Transaction\ResiStockCorrectionController@add');
            Route::get('edit/{id}', 'Transaction\ResiStockCorrectionController@edit');
            Route::post('save', 'Transaction\ResiStockCorrectionController@save');
            Route::get('get-json-resi', 'Transaction\ResiStockCorrectionController@getJsonResi');
        });
        Route::group(['prefix' => 'receipt-or-return-delivery-order'], function() {
            Route::any('', 'Transaction\ReceiptOrReturnDeliveryOrderController@index');
            Route::get('add', 'Transaction\ReceiptOrReturnDeliveryOrderController@add');
            Route::get('edit/{id}', 'Transaction\ReceiptOrReturnDeliveryOrderController@edit');
            Route::post('save', 'Transaction\ReceiptOrReturnDeliveryOrderController@save');
            Route::get('get-json-delivery-order', 'Transaction\ReceiptOrReturnDeliveryOrderController@getJsonDeliveryOrder');
        });
        Route::group(['prefix' => 'customer-taking'], function() {
            Route::any('', 'Transaction\CustomerTakingController@index');
            Route::get('add', 'Transaction\CustomerTakingController@add');
            Route::get('edit/{id}', 'Transaction\CustomerTakingController@edit');
            Route::post('save', 'Transaction\CustomerTakingController@save');
            Route::get('print-pdf-detail/{id}', 'Transaction\CustomerTakingController@printPdfDetail');
            Route::get('get-json-resi', 'Transaction\CustomerTakingController@getJsonResi');
        });
        Route::group(['prefix' => 'customer-taking-transact'], function() {
            Route::any('', 'Transaction\CustomerTakingTransactController@index');
            Route::get('add/{id?}', 'Transaction\CustomerTakingTransactController@add');
            Route::get('edit/{id}', 'Transaction\CustomerTakingTransactController@edit');
            Route::post('save', 'Transaction\CustomerTakingTransactController@save');
            Route::get('get-json-customer-taking', 'Transaction\CustomerTakingTransactController@getJsonCustomerTaking');
        });
        Route::group(['prefix' => 'official-report'], function() {
            Route::any('', 'Transaction\OfficialReportController@index');
            Route::get('add', 'Transaction\OfficialReportController@add');
            Route::get('edit/{id}', 'Transaction\OfficialReportController@edit');
            Route::post('save', 'Transaction\OfficialReportController@save');
            Route::get('get-json-resi', 'Transaction\OfficialReportController@getJsonResi');
        });
        Route::group(['prefix' => 'approve-official-report'], function() {
            Route::any('', 'Transaction\ApproveOfficialReportController@index');
            Route::get('edit/{id}', 'Transaction\ApproveOfficialReportController@edit');
            Route::post('save', 'Transaction\ApproveOfficialReportController@save');
        });
        Route::group(['prefix' => 'document-transfer'], function() {
            Route::get('add', 'Transaction\DocumentTransferController@add');
            Route::any('', 'Transaction\DocumentTransferController@index');
            Route::get('edit/{id}', 'Transaction\DocumentTransferController@edit');
            Route::post('save', 'Transaction\DocumentTransferController@save');
            Route::post('cancel', 'Transaction\DocumentTransferController@cancel');
            Route::post('close', 'Transaction\DocumentTransferController@close');
            Route::get('print-pdf-detail/{id}', 'Transaction\DocumentTransferController@printPdfDetail');
            Route::get('get-json-resi', 'Transaction\DocumentTransferController@getJsonResi');
        });
        Route::group(['prefix' => 'receipt-document-transfer'], function() {
            Route::get('add', 'Transaction\ReceiptDocumentTransferController@add');
            Route::any('', 'Transaction\ReceiptDocumentTransferController@index');
            Route::get('edit/{id}', 'Transaction\ReceiptDocumentTransferController@edit');
            Route::post('save', 'Transaction\ReceiptDocumentTransferController@save');
            Route::post('cancel', 'Transaction\ReceiptDocumentTransferController@cancel');
            Route::get('print-pdf-detail/{id}', 'Transaction\ReceiptDocumentTransferController@printPdfDetail');
        });
    });
    Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'checklist-manifest'], function() {
            Route::any('', 'Report\ChecklistManifestController@index');
            Route::get('print-pdf-checklist/{id}', 'Report\ChecklistManifestController@printPdfChecklist');
        });
        Route::group(['prefix' => 'manifest-arrived'], function() {
            Route::any('', 'Report\ManifestArrivedController@index');
            Route::get('edit/{id}', 'Report\ManifestArrivedController@edit');
            Route::get('print-excel-index', 'Report\ManifestArrivedController@printExcelIndex');
        });
        Route::group(['prefix' => 'resi-outstanding'], function() {
            Route::any('', 'Report\ResiOutstandingController@index');
            Route::get('print-excel', 'Report\ResiOutstandingController@printExcel');
        });
        Route::group(['prefix' => 'vehicle-moving'], function() {
            Route::any('', 'Report\VehicleMovingController@index');
            Route::get('print-pdf-index', 'Report\VehicleMovingController@printPdfIndex');
            Route::get('print-excel-index', 'Report\VehicleMovingController@printExcelIndex');
        });
        Route::group(['prefix' => 'delivery-order-outstanding'], function() {
            Route::any('', 'Report\DeliveryOrderOutstandingController@index');
            Route::get('edit/{id}', 'Report\DeliveryOrderOutstandingController@edit');
            Route::get('print-excel-index', 'Report\DeliveryOrderOutstandingController@printExcelIndex');
        });
        Route::group(['prefix' => 'resi-do-partner'], function() {
            Route::get('', 'Report\ResiDOPartnerController@index');
            Route::post('print-excel', 'Report\ResiDOPartnerController@printExcel');
        });
        Route::group(['prefix' => 'resi-to-receipt'], function() {
            Route::get('', 'Report\ResiToReceiptController@index');
            Route::post('print-excel', 'Report\ResiToReceiptController@printExcel');
        });
        Route::group(['prefix' => 'history-resi'], function() {
            Route::any('', 'Report\HistoryResiController@index');
        });
        Route::group(['prefix' => 'tracking-resi'], function() {
            Route::any('', 'Report\TrackingResiController@index');
        });
        Route::group(['prefix' => 'resi-all-branch'], function() {
            Route::any('', 'Report\ResiAllBranchController@index');
            Route::get('edit/{id}', 'Report\ResiAllBranchController@edit');
            Route::get('print-excel-index', 'Report\ResiAllBranchController@printExcelIndex');
        });
    });
});
