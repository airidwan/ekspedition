<?php

return [
    'label' => 'marketing/menu.marketing',
    'icon' => 'users',
    'children' =>
    [
        [
            'label' => 'shared/menu.transaction',
            'children' =>
            [
                [
                    'label' => 'marketing/menu.operator-book',
                    'route' => 'marketing/transaction/operator-book',
                    'resource' => 'Marketing\Transaction\OperatorBook',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'marketing/menu.complain',
                    'route' => 'marketing/transaction/complain',
                    'resource' => 'Marketing\Transaction\Complain',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'marketing/menu.pickup-request',
                    'route' => 'marketing/transaction/pickup-request',
                    'resource' => 'Marketing\Transaction\PickupRequest',
                    'privilege' => 'view',
                ],
            ]
        ],
    ]
];
