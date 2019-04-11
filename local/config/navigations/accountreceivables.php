<?php

return [
    'label' => 'accountreceivables/menu.accountreceivables',
    'icon'  => 'credit-card',
    'children'=>
    [
        [
            'label' => 'shared/menu.transaction',
            'children' =>
            [
                [
                    'label' => 'accountreceivables/menu.invoice',
                    'route' => 'accountreceivables/transaction/invoice',
                    'resource' => 'Accountreceivables\Transaction\Invoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'accountreceivables/menu.batch-invoice',
                    'route' => 'accountreceivables/transaction/batch-invoice',
                    'resource' => 'Accountreceivables\Transaction\BatchInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'accountreceivables/menu.approve-invoice',
                    'route' => 'accountreceivables/transaction/approve-invoice',
                    'resource' => 'Accountreceivables\Transaction\ApproveInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'accountreceivables/menu.approve-batch-invoice',
                    'route' => 'accountreceivables/transaction/approve-batch-invoice',
                    'resource' => 'Accountreceivables\Transaction\ApproveBatchInvoice',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'accountreceivables/menu.cek-giro',
                    'route' => 'accountreceivables/transaction/cek-giro',
                    'resource' => 'Accountreceivables\Transaction\CekGiro',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'accountreceivables/menu.receipt',
                    'route' => 'accountreceivables/transaction/receipt',
                    'resource' => 'Accountreceivables\Transaction\Receipt',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'accountreceivables/menu.receipt-other',
                    'route' => 'accountreceivables/transaction/receipt-other',
                    'resource' => 'Accountreceivables\Transaction\ReceiptOther',
                    'privilege' => 'view'
                ],
            ]
        ],
        [
            'label' => 'shared/menu.report',
            'children' =>
            [
                [
                    'label' => 'accountreceivables/menu.cash-in',
                    'route' => 'accountreceivables/report/cash-in',
                    'resource' => 'Accountreceivables\Report\CashIn',
                    'privilege' => 'view'
                ],
            ]
        ],
    ]
];
