<?php

return [
    'label' => 'asset/menu.asset-management',
    'icon' => 'folder',
    'children' =>
    [
        [
            'label' => 'shared/menu.master',
            'children' =>
            [
                [
                    'label' => 'asset/menu.asset-category',
                    'route' => 'asset/master/asset-category',
                    'resource' => 'Asset\Master\AssetCategory',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.transaction',
            'children' =>
            [
                 [
                    'label' => 'asset/menu.mask-add-asset',
                    'route' => 'asset/transaction/mass-addition-asset',
                    'resource' => 'Asset\Transaction\MassAdditionAsset',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'asset/menu.addition-asset',
                    'route' => 'asset/transaction/addition-asset',
                    'resource' => 'Asset\Transaction\AdditionAsset',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'asset/menu.service-asset',
                    'route' => 'asset/transaction/service-asset',
                    'resource' => 'Asset\Transaction\ServiceAsset',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'asset/menu.service-truck-monthly',
                    'route' => 'asset/transaction/service-truck-monthly',
                    'resource' => 'Asset\Transaction\ServiceTruckMonthly',
                    'privilege' => 'view',
                ],
            ]
        ],
        [
            'label' => 'shared/menu.report',
            'children' =>
            [
                 [
                    'label' => 'asset/menu.asset-maintenance',
                    'route' => 'asset/report/asset-maintenance',
                    'resource' => 'Asset\Report\AssetMaintenance',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'asset/menu.truck-monthly-maintenance',
                    'route' => 'asset/report/truck-monthly-maintenance',
                    'resource' => 'Asset\Report\TruckMonthlyMaintenance',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'asset/menu.all-addition-asset',
                    'route' => 'asset/report/all-addition-asset',
                    'resource' => 'Asset\Report\AllAdditionAsset',
                    'privilege' => 'view',
                ],
            ]
        ],
    ]
];
