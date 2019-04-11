<?php

return [
    'label' => 'purchasing/menu.purchasing',
    'icon' => 'shopping-cart',
    'children' =>
    [
        [
            'label' => 'shared/menu.master',
            'children' =>
            [
                [
                    'label' => 'purchasing/menu.master-type-po',
                    'route' => 'purchasing/master/master-type-po',
                    'resource' => 'Purchasing\Master\MasterTypePo',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.transaksi',
            'children' =>
            [
                [
                    'label' => 'purchasing/menu.purchase-order',
                    'route' => 'purchasing/transaction/purchase-order',
                    'resource' => 'Purchasing\Transaction\PurchaseOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'purchasing/menu.purchase-approve',
                    'route' => 'purchasing/transaction/purchase-approve',
                    'resource' => 'Purchasing\Transaction\PurchaseApprove',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.report',
            'children' =>
            [
                [
                    'label' => 'purchasing/menu.purchase-order-outstanding',
                    'route' => 'purchasing/report/purchase-order-outstanding',
                    'resource' => 'Purchasing\Report\PurchaseOrderOutstanding',
                    'privilege' => 'view',
                ],
            ]
        ],
    ]
];
    