<?php

return [
    'sysAdmin' => [
        'master' => [
            'SysAdmin\Master\User' => ['view', 'insert', 'update'],
            'SysAdmin\Master\Role' => ['view', 'insert', 'update'],
        ]
    ],
    'marketing' => [
        'transaction' => [
            'Marketing\Transaction\OperatorBook' => ['view', 'insert', 'update', 'delete'],
            'Marketing\Transaction\Complain' => ['view', 'insert', 'update', 'delete'],
            'Marketing\Transaction\PickupRequest' => ['view', 'insert', 'update', 'approve', 'cancel'],
        ],
    ],
    'operational' => [
        'master' => [
            'Operational\Master\MasterOrganization' => ['view', 'update'],
            'Operational\Master\MasterBranch' => ['view', 'insert', 'update'],
            'Operational\Master\MasterCommodity' => ['view', 'insert', 'update'],
            'Operational\Master\MasterRegion' => ['view', 'insert', 'update'],
            'Operational\Master\MasterCustomer' => ['view', 'insert', 'update'],
            'Operational\Master\MasterDriver' => ['view', 'insert', 'update'],
            'Operational\Master\MasterTruckBrand' => ['view', 'insert', 'update'],
            'Operational\Master\MasterTruckType' => ['view', 'insert', 'update'],
            'Operational\Master\MasterTruck' => ['view', 'insert', 'update'],
            'Operational\Master\MasterRoutesRates' => ['view', 'insert', 'update'],
            'Operational\Master\MasterManifestMoneyTrip' => ['view', 'insert', 'update'],
            'Operational\Master\MasterDeliveryArea' => ['view', 'insert', 'update'],
            'Operational\Master\MasterDeliveryAreaMoneyTrip' => ['view', 'insert', 'update'],
            'Operational\Master\MasterRentCar' => ['view', 'insert', 'update'],
            'Operational\Master\MasterShippingPrice' => ['view', 'insert', 'update'],
            'Operational\Master\MasterManifestDriverSalary' => ['view', 'insert', 'update'],
            'Operational\Master\MasterDoPickupDriverSalary' => ['view', 'insert', 'update'],
            'Operational\Master\MasterAlertResiStock' => ['view', 'update'],
            'Operational\Master\MasterCity' => ['view', 'insert', 'update'],
        ],
        'transaction' => [
            'Operational\Transaction\PickupForm' => ['view', 'insert', 'update', 'cancel'],
            'Operational\Transaction\TransactionResi' => ['view', 'insert', 'update', 'delete', 'nego', 'approve'],
            'Operational\Transaction\ApproveNegoResi' => ['view', 'approve', 'reject'],
            'Operational\Transaction\Manifest' => ['view', 'insert', 'update', 'delete', 'close'],
            'Operational\Transaction\ApproveManifest' => ['view', 'update', 'approve', 'reject', 'cancel'],
            'Operational\Transaction\MoneyTripManifest' => ['view', 'update', 'cancel'],
            'Operational\Transaction\ShipmentManifest' => ['view', 'update'],
            'Operational\Transaction\ArriveManifest' => ['view', 'update'],
            'Operational\Transaction\ReceiptManifest' => ['view', 'insert', 'update'],
            'Operational\Transaction\ReturnManifest' => ['view', 'insert', 'update'],
            'Operational\Transaction\StockResi' => ['view'],
            'Operational\Transaction\WarehouseDeliveryList' => ['view', 'update'],
            'Operational\Transaction\DraftDeliveryOrder' => ['view', 'insert', 'update'],
            'Operational\Transaction\DeliveryOrder' => ['view', 'insert', 'request-approval', 'update', 'cancel'],
            'Operational\Transaction\ApproveDeliveryOrder' => ['view', 'update', 'approve', 'reject'],
            'Operational\Transaction\CostDeliveryOrder' => ['view', 'update', 'otr', 'reject', 'cancel'],
            'Operational\Transaction\ReceiptOrReturnDeliveryOrder' => ['view', 'insert'],
            'Operational\Transaction\LetterOfGoodExpenditure' => ['view', 'insert', 'update'],
            'Operational\Transaction\LetterOfGoodExpenditureTransact' => ['view', 'insert', 'update'],
            'Operational\Transaction\OfficialReport' => ['view', 'insert', 'update'],
            'Operational\Transaction\ApproveOfficialReport' => ['view', 'approve', 'update'],
            'Operational\Transaction\ResiStockCorrection' => ['view', 'insert', 'update'],
            'Operational\Transaction\DocumentTransfer' => ['view', 'insert', 'update', 'cancel', 'transact', 'close'],
            'Operational\Transaction\ReceiptDocumentTransfer' => ['view', 'insert', 'update'],
        ],
        'report' => [
            'Operational\Report\ChecklistManifest' => ['view'],
            'Operational\Report\ManifestArrived' => ['view'],
            'Operational\Report\ResiOutstanding' => ['view'],
            'Operational\Report\VehicleMoving' => ['view'],
            'Operational\Report\DeliveryOrderOutstanding' => ['view', 'update'],
            'Operational\Report\ResiDOPartner' => ['view'],
            'Operational\Report\ResiToReceipt' => ['view'],
            'Operational\Report\HistoryResi' => ['view'],
            'Operational\Report\TrackingResi' => ['view'],
            'Operational\Report\ResiAllBranch' => ['view'],
        ],
    ],
    'accountreceivables' => [
        'transaction' => [
            'Accountreceivables\Transaction\Invoice' => ['view', 'insert', 'update', 'cancel'],
            'Accountreceivables\Transaction\BatchInvoice' => ['view', 'insert', 'update'],
            'Accountreceivables\Transaction\ApproveInvoice' => ['view', 'update'],
            'Accountreceivables\Transaction\ApproveBatchInvoice' => ['view', 'update'],
            'Accountreceivables\Transaction\CekGiro' => ['view', 'insert', 'update', 'cancel'],
            'Accountreceivables\Transaction\Receipt' => ['view', 'insert'],
            'Accountreceivables\Transaction\ReceiptOther' => ['view', 'insert'],
        ],
        'report' => [
            'Accountreceivables\Report\CashIn' => ['view'],
        ],
    ],
    'purchasing' => [
        'master' => [
            'Purchasing\Master\MasterTypePo' => ['view', 'insert', 'update'],
        ],
        'transaction' => [
            'Purchasing\Transaction\PurchaseOrder' => ['view', 'insert', 'update', 'delete', 'approveAdmin', 'approveKacab'],
            'Purchasing\Transaction\PurchaseApprove' => ['view', 'approve', 'reject', 'update'],
        ],
        'report' => [
            'Purchasing\Report\PurchaseOrderOutstanding' => ['view'],
        ],
    ],
    'inventory' => [
        'master' => [
            'Inventory\Master\MasterUom' => ['view', 'insert', 'update'],
            'Inventory\Master\MasterCategory' => ['view', 'insert', 'update'],
            'Inventory\Master\MasterItem' => ['view', 'insert', 'update'],
            'Inventory\Master\MasterWarehouse' => ['view', 'insert', 'update'],
        ],
        'transaction' => [
            'Inventory\Transaction\StockItem' => ['view'],
            'Inventory\Transaction\MoveOrder' => ['view', 'insert', 'update', 'cancel', 'transact'],
            'Inventory\Transaction\AdjustmentStock' => ['view', 'insert', 'update', 'cancel', 'transact'],
            'Inventory\Transaction\WarehouseTransfer' => ['view', 'insert', 'update', 'cancel', 'transact'],
            'Inventory\Transaction\BranchTransfer' => ['view', 'insert', 'update', 'cancel', 'transact', 'close'],
            'Inventory\Transaction\ReceiptBranchTransfer' => ['view', 'insert', 'update'],
            'Inventory\Transaction\ReceiptPurchaseOrder' => ['view', 'insert', 'update'],
            'Inventory\Transaction\ReturnPurchaseOrder' => ['view', 'insert','update'],
        ],
        'report' => [
            'Inventory\Report\MoveOrderAllBranch' => ['view'],
        ]
    ],
    'asset' => [
        'master' => [
            'Asset\Master\AssetCategory' => ['view', 'insert', 'update','delete'],
        ],
        'transaction' => [
            'Asset\Transaction\MassAdditionAsset' => ['view'],
            'Asset\Transaction\AdditionAsset' => ['view', 'insert', 'update'],
            'Asset\Transaction\ServiceAsset' => ['view', 'insert', 'update'],
            'Asset\Transaction\ServiceTruckMonthly' => ['view', 'insert', 'update'],
        ],
        'report' => [
            'Asset\Report\AssetMaintenance' => ['view'],
            'Asset\Report\TruckMonthlyMaintenance' => ['view'],
            'Asset\Report\AllAdditionAsset' => ['view'],
        ],
    ],
    'payable' => [
        'master' => [
            'Payable\Master\MasterVendor' => ['view', 'insert', 'update'],
            'Payable\Master\MasterApType' => ['view', 'insert', 'update'],
        ],
        'transaction' => [
            'Payable\Transaction\DpInvoice' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\PurchaseOrderInvoice' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\DriverSalaryInvoice' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\ManifestMoneyTripInvoice' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\DoPickupMoneyTripInvoice' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\DoPartnerInvoice' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\ServiceInvoice' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\KasbonInvoice' => ['view', 'insert', 'update', 'approveAdmin', 'approveKacab', 'cancel'],
            'Payable\Transaction\ApproveKasbonInvoice' => ['view', 'update', 'approve', 'reject'],
            'Payable\Transaction\OtherInvoice' => ['view', 'insert', 'update', 'approveAdmin', 'approveKacab', 'cancel'],
            'Payable\Transaction\ApproveOtherInvoice' => ['view', 'update', 'approve', 'reject'],
            'Payable\Transaction\Payment' => ['view', 'insert', 'update', 'approve', 'cancel'],
            'Payable\Transaction\InjectManifestDriverAndAssistantSalary' => ['view', 'update'],
            'Payable\Transaction\InjectDoDriverAndAssistantSalary' => ['view', 'update'],
            'Payable\Transaction\InjectPickupDriverAndAssistantSalary' => ['view', 'update'],
        ],
        'report' => [
            'Payable\Report\RemainingDriverKasbon' => ['view'],
            'Payable\Report\RemainingEmployeeKasbon' => ['view'],
            'Payable\Report\KasbonHistory' => ['view'],
            'Payable\Report\PurchaseOrder' => ['view'],
            'Payable\Report\PurchaseOrderCredit' => ['view'],
            'Payable\Report\CashOut' => ['view'],
        ],
    ],
    'general-ledger' => [
        'master' => [
            'Generalledger\Master\MasterCoa' => ['view', 'insert', 'update'],
            'Generalledger\Master\MasterCoaCombination' => ['view', 'insert', 'update'],
            'Generalledger\Master\MasterBank' => ['view', 'insert', 'update'],
            'Generalledger\Master\SettingJournal' => ['view', 'update'],
        ],
        'transaction' => [
            'Generalledger\Transaction\JournalEntry' => ['view', 'insert', 'update', 'reserve', 'post', 'postAll', 'viewSalary'],
        ],
        'report' => [
            'Generalledger\Report\DailyCash' => ['view'],
            'Generalledger\Report\JournalEntries' => ['view'],
            'Generalledger\Report\AccountPost' => ['view'],
            'Generalledger\Report\IncomeStatement' => ['view'],
            'Generalledger\Report\BalanceSheet' => ['view'],
        ],
    ],
];