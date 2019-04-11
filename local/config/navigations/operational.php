<?php

return [
    'label' => 'operational/menu.operational',
    'icon' => 'truck',
    'children' =>
    [
        [
            'label' => 'shared/menu.master',
            'children' =>
            [
                [
                    'label' => 'operational/menu.organization',
                    'route' => 'operational/master/master-organization',
                    'resource' => 'Operational\Master\MasterOrganization',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.branch',
                    'route' => 'operational/master/master-branch',
                    'resource' => 'Operational\Master\MasterBranch',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.commodity',
                    'route' => 'operational/master/master-commodity',
                    'resource' => 'Operational\Master\MasterCommodity',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.region',
                    'route' => 'operational/master/master-region',
                    'resource' => 'Operational\Master\MasterRegion',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.customer',
                    'route' => 'operational/master/master-customer',
                    'resource' => 'Operational\Master\MasterCustomer',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.driver',
                    'route' => 'operational/master/master-driver',
                    'resource' => 'Operational\Master\MasterDriver',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.truck-brand',
                    'route' => 'operational/master/master-truck-brand',
                    'resource' => 'Operational\Master\MasterTruckBrand',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.truck-type',
                    'route' => 'operational/master/master-truck-type',
                    'resource' => 'Operational\Master\MasterTruckType',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.truck',
                    'route' => 'operational/master/master-truck',
                    'resource' => 'Operational\Master\MasterTruck',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.routes-rates',
                    'route' => 'operational/master/master-routes-rates',
                    'resource' => 'Operational\Master\MasterRoutesRates',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.money-trip',
                    'route' => 'operational/master/master-money-trip',
                    'resource' => 'Operational\Master\MasterManifestMoneyTrip',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.delivery-area',
                    'route' => 'operational/master/master-delivery-area',
                    'resource' => 'Operational\Master\MasterDeliveryArea',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.delivery-area-money-trip',
                    'route' => 'operational/master/master-delivery-area-money-trip',
                    'resource' => 'Operational\Master\MasterDeliveryAreaMoneyTrip',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.rent-car',
                    'route' => 'operational/master/master-rent-car',
                    'resource' => 'Operational\Master\MasterRentCar',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.shipping-price',
                    'route' => 'operational/master/master-shipping-price',
                    'resource' => 'Operational\Master\MasterShippingPrice',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.driver-salary',
                    'route' => 'operational/master/master-driver-salary',
                    'resource' => 'Operational\Master\MasterManifestDriverSalary',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.do-pickup-driver-salary',
                    'route' => 'operational/master/master-do-pickup-driver-salary',
                    'resource' => 'Operational\Master\MasterDoPickupDriverSalary',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.alert-resi-stock',
                    'route' => 'operational/master/master-alert-resi-stock',
                    'resource' => 'Operational\Master\MasterAlertResiStock',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.city',
                    'route' => 'operational/master/master-city',
                    'resource' => 'Operational\Master\MasterCity',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.transaction',
            'children' =>
            [
                [
                    'label' => 'operational/menu.pickup-form',
                    'route' => 'operational/transaction/pickup-form',
                    'resource' => 'Operational\Transaction\PickupForm',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.resi',
                    'route' => 'operational/transaction/transaction-resi',
                    'resource' => 'Operational\Transaction\TransactionResi',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.approve-nego-resi',
                    'route' => 'operational/transaction/approve-nego-resi',
                    'resource' => 'Operational\Transaction\ApproveNegoResi',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.manifest',
                    'route' => 'operational/transaction/manifest',
                    'resource' => 'Operational\Transaction\Manifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.approve-manifest',
                    'route' => 'operational/transaction/approve-manifest',
                    'resource' => 'Operational\Transaction\ApproveManifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.money-trip-manifest',
                    'route' => 'operational/transaction/money-trip-manifest',
                    'resource' => 'Operational\Transaction\MoneyTripManifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.shipment-manifest',
                    'route' => 'operational/transaction/shipment-manifest',
                    'resource' => 'Operational\Transaction\ShipmentManifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.arrive-manifest',
                    'route' => 'operational/transaction/arrive-manifest',
                    'resource' => 'Operational\Transaction\ArriveManifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.receipt-manifest',
                    'route' => 'operational/transaction/receipt-manifest',
                    'resource' => 'Operational\Transaction\ReceiptManifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.return-manifest',
                    'route' => 'operational/transaction/return-manifest',
                    'resource' => 'Operational\Transaction\ReturnManifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.stock-resi',
                    'route' => 'operational/transaction/stock-resi',
                    'resource' => 'Operational\Transaction\StockResi',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.resi-stock',
                    'route' => 'operational/transaction/wdl',
                    'resource' => 'Operational\Transaction\WarehouseDeliveryList',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.draft-delivery-order',
                    'route' => 'operational/transaction/draft-delivery-order',
                    'resource' => 'Operational\Transaction\DraftDeliveryOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.delivery-order',
                    'route' => 'operational/transaction/delivery-order',
                    'resource' => 'Operational\Transaction\DeliveryOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.approve-delivery-order',
                    'route' => 'operational/transaction/approve-delivery-order',
                    'resource' => 'Operational\Transaction\ApproveDeliveryOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.cost-delivery-order',
                    'route' => 'operational/transaction/cost-delivery-order',
                    'resource' => 'Operational\Transaction\CostDeliveryOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.receipt-or-return-delivery-order',
                    'route' => 'operational/transaction/receipt-or-return-delivery-order',
                    'resource' => 'Operational\Transaction\ReceiptOrReturnDeliveryOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.customer-taking',
                    'route' => 'operational/transaction/customer-taking',
                    'resource' => 'Operational\Transaction\LetterOfGoodExpenditure',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.customer-taking-transact',
                    'route' => 'operational/transaction/customer-taking-transact',
                    'resource' => 'Operational\Transaction\LetterOfGoodExpenditureTransact',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.official-report',
                    'route' => 'operational/transaction/official-report',
                    'resource' => 'Operational\Transaction\OfficialReport',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.approve-official-report',
                    'route' => 'operational/transaction/approve-official-report',
                    'resource' => 'Operational\Transaction\ApproveOfficialReport',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.resi-stock-correction',
                    'route' => 'operational/transaction/resi-stock-correction',
                    'resource' => 'Operational\Transaction\ResiStockCorrection',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.document-transfer',
                    'route' => 'operational/transaction/document-transfer',
                    'resource' => 'Operational\Transaction\DocumentTransfer',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.receipt-document-transfer',
                    'route' => 'operational/transaction/receipt-document-transfer',
                    'resource' => 'Operational\Transaction\ReceiptDocumentTransfer',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.report',
            'children' =>
            [
                [
                    'label' => 'operational/menu.checklist-manifest',
                    'route' => 'operational/report/checklist-manifest',
                    'resource' => 'Operational\Report\ChecklistManifest',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.manifest-arrived',
                    'route' => 'operational/report/manifest-arrived',
                    'resource' => 'Operational\Report\ManifestArrived',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.resi-outstanding',
                    'route' => 'operational/report/resi-outstanding',
                    'resource' => 'Operational\Report\ResiOutstanding',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.vehicle-moving',
                    'route' => 'operational/report/vehicle-moving',
                    'resource' => 'Operational\Report\VehicleMoving',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.delivery-order-outstanding',
                    'route' => 'operational/report/delivery-order-outstanding',
                    'resource' => 'Operational\Report\DeliveryOrderOutstanding',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.resi-do-partner',
                    'route' => 'operational/report/resi-do-partner',
                    'resource' => 'Operational\Report\ResiDOPartner',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.resi-to-receipt',
                    'route' => 'operational/report/resi-to-receipt',
                    'resource' => 'Operational\Report\ResiToReceipt',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.history-resi',
                    'route' => 'operational/report/history-resi',
                    'resource' => 'Operational\Report\HistoryResi',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.tracking-resi',
                    'route' => 'operational/report/tracking-resi',
                    'resource' => 'Operational\Report\TrackingResi',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'operational/menu.resi-all-branch',
                    'route' => 'operational/report/resi-all-branch',
                    'resource' => 'Operational\Report\ResiAllBranch',
                    'privilege' => 'view',
                ],
            ]
        ],
    ]
];
