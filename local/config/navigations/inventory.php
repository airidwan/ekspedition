<?php

return [
    'label' => 'inventory/menu.inventory',
    'icon' => 'briefcase',
    'children' =>
    [
        [
            'label' => 'shared/menu.master',
            'children' =>
            [
                [
                    'label' => 'inventory/menu.master-uom',
                    'route' => 'inventory/master/master-uom',
                    'resource' => 'Inventory\Master\MasterUom',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.master-category',
                    'route' => 'inventory/master/master-category',
                    'resource' => 'Inventory\Master\MasterCategory',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.master-item',
                    'route' => 'inventory/master/master-item',
                    'resource' => 'Inventory\Master\MasterItem',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.master-warehouse',
                    'route' => 'inventory/master/master-warehouse',
                    'resource' => 'Inventory\Master\MasterWarehouse',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.transaction',
            'children' =>
            [
                [
                    'label' => 'inventory/menu.master-stock',
                    'route' => 'inventory/transaction/stock-item',
                    'resource' => 'Inventory\Transaction\StockItem',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.move-order',
                    'route' => 'inventory/transaction/move-order',
                    'resource' => 'Inventory\Transaction\MoveOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.adjustment-stock',
                    'route' => 'inventory/transaction/adjustment-stock',
                    'resource' => 'Inventory\Transaction\AdjustmentStock',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.warehouse-transfer',
                    'route' => 'inventory/transaction/warehouse-transfer',
                    'resource' => 'Inventory\Transaction\WarehouseTransfer',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.branch-transfer',
                    'route' => 'inventory/transaction/branch-transfer',
                    'resource' => 'Inventory\Transaction\BranchTransfer',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.receipt-branch-transfer',
                    'route' => 'inventory/transaction/receipt-branch-transfer',
                    'resource' => 'Inventory\Transaction\ReceiptBranchTransfer',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.receipt-po',
                    'route' => 'inventory/transaction/receipt-po',
                    'resource' => 'Inventory\Transaction\ReceiptPurchaseOrder',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'inventory/menu.return-po',
                    'route' => 'inventory/transaction/return-po',
                    'resource' => 'Inventory\Transaction\ReturnPurchaseOrder',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.report',
            'children' =>
            [
                [
                    'label' => 'inventory/menu.move-order-all-branch',
                    'route' => 'inventory/report/move-order-all-branch',
                    'resource' => 'Inventory\Report\MoveOrderAllBranch',
                    'privilege' => 'view',
                ],
            ]
        ]
    ]
];
