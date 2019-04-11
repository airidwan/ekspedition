<?php

return [
    [
        'label' => 'shared/menu.home',
        'icon' => 'home',
        'route' => '',
    ],
    [
        'label' => 'shared/menu.notification',
        'icon' => 'envelope',
        'route' => 'notification',
    ],
    include('sys-admin.php'),
    include('marketing.php'),
    include('operational.php'),
    include('accountreceivables.php'),
    include('purchasing.php'),
    include('inventory.php'),
    include('asset.php'),
    include('payable.php'),
    include('general-ledger.php'),
];
