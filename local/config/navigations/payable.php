<?php

return [
    'label' => 'payable/menu.payable',
    'icon' => 'usd',
    'children'=>
    [
        [
        'label' => 'shared/menu.master',
        'children' =>
            [
                [
                    'label' => 'payable/menu.vendor-supplier',
                    'route' => 'payable/master/master-vendor',
                    'resource' => 'Payable\Master\MasterVendor',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.ap-type',
                    'route' => 'payable/master/master-ap-type',
                    'resource' => 'Payable\Master\MasterApType',
                    'privilege' => 'view'
                ],
            ]
        ],
        [
        'label' => 'shared/menu.transaction',
        'children' =>
            [
                [
                    'label' => 'payable/menu.dp-invoice',
                    'route' => 'payable/transaction/dp-invoice',
                    'resource' => 'Payable\Transaction\DpInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.po-invoice',
                    'route' => 'payable/transaction/po-invoice',
                    'resource' => 'Payable\Transaction\PurchaseOrderInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.driver-salary-invoice',
                    'route' => 'payable/transaction/driver-salary-invoice',
                    'resource' => 'Payable\Transaction\DriverSalaryInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.manifest-money-trip-invoice',
                    'route' => 'payable/transaction/manifest-money-trip-invoice',
                    'resource' => 'Payable\Transaction\ManifestMoneyTripInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.do-pickup-money-trip-invoice',
                    'route' => 'payable/transaction/do-pickup-money-trip-invoice',
                    'resource' => 'Payable\Transaction\DoPickupMoneyTripInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.do-partner-invoice',
                    'route' => 'payable/transaction/do-partner-invoice',
                    'resource' => 'Payable\Transaction\DoPartnerInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.service-invoice',
                    'route' => 'payable/transaction/service-invoice',
                    'resource' => 'Payable\Transaction\ServiceInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.debt-employee-invoice',
                    'route' => 'payable/transaction/debt-employee-invoice',
                    'resource' => 'Payable\Transaction\KasbonInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.approve-debt-employee-invoice',
                    'route' => 'payable/transaction/approve-debt-employee-invoice',
                    'resource' => 'Payable\Transaction\ApproveKasbonInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.other-invoice',
                    'route' => 'payable/transaction/other-invoice',
                    'resource' => 'Payable\Transaction\OtherInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.approve-other-invoice',
                    'route' => 'payable/transaction/approve-other-invoice',
                    'resource' => 'Payable\Transaction\ApproveOtherInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.payment',
                    'route' => 'payable/transaction/payment',
                    'resource' => 'Payable\Transaction\Payment',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.inject-manifest-driver-and-assistant-salary',
                    'route' => 'payable/transaction/inject-manifest-driver-and-assistant-salary',
                    'resource' => 'Payable\Transaction\InjectManifestDriverAndAssistantSalary',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.inject-do-driver-and-assistant-salary',
                    'route' => 'payable/transaction/inject-do-driver-and-assistant-salary',
                    'resource' => 'Payable\Transaction\InjectDoDriverAndAssistantSalary',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'payable/menu.inject-pickup-driver-and-assistant-salary',
                    'route' => 'payable/transaction/inject-pickup-driver-and-assistant-salary',
                    'resource' => 'Payable\Transaction\InjectPickupDriverAndAssistantSalary',
                    'privilege' => 'view'
                ],
            ]
        ],
        [
        'label' => 'shared/menu.report',
        'children' =>
            [
                [
                    'label'     => 'payable/menu.remaining-driver-kasbon',
                    'route'     => 'payable/report/remaining-driver-kasbon',
                    'resource'  => 'Payable\Report\RemainingDriverKasbon',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'payable/menu.remaining-employee-kasbon',
                    'route'     => 'payable/report/remaining-employee-kasbon',
                    'resource'  => 'Payable\Report\RemainingEmployeeKasbon',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'payable/menu.kasbon-history',
                    'route'     => 'payable/report/kasbon-history',
                    'resource'  => 'Payable\Report\KasbonHistory',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'payable/menu.purchase-order',
                    'route'     => 'payable/report/purchase-order',
                    'resource'  => 'Payable\Report\PurchaseOrder',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'payable/menu.purchase-order-credit',
                    'route'     => 'payable/report/purchase-order-credit',
                    'resource'  => 'Payable\Report\PurchaseOrderCredit',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'payable/menu.cash-out',
                    'route'     => 'payable/report/cash-out',
                    'resource'  => 'Payable\Report\CashOut',
                    'privilege' => 'view'
                ],
            ]
        ],
    ]
];
