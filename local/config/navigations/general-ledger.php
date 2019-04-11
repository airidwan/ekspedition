<?php

return [
    'label' => 'general-ledger/menu.general-ledger',
    'icon'  => 'money',
    'children'=>
    [
        [
        'label' => 'shared/menu.master',
        'children' =>
            [
                [
                    'label' => 'general-ledger/menu.coa',
                    'route' => 'general-ledger/master/master-coa',
                    'resource' => 'Generalledger\Master\MasterCoa',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'general-ledger/menu.coa-combination',
                    'route' => 'general-ledger/master/master-coa-combination',
                    'resource' => 'Generalledger\Master\MasterCoaCombination',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'general-ledger/menu.master-bank',
                    'route' => 'general-ledger/master/master-bank',
                    'resource' => 'Generalledger\Master\MasterBank',
                    'privilege' => 'view'
                ],
                [
                    'label' => 'general-ledger/menu.setting-journal',
                    'route' => 'general-ledger/master/setting-journal',
                    'resource' => 'Generalledger\Master\SettingJournal',
                    'privilege' => 'view'
                ],
            ]
        ],
        [
        'label' => 'shared/menu.transaksi',
        'children' =>
            [
                [
                    'label' => 'general-ledger/menu.journal-entry',
                    'route' => 'general-ledger/transaction/journal-entry',
                    'resource' => 'Generalledger\Transaction\JournalEntry',
                    'privilege' => 'view'
                ],
            ]
        ],
        [
        'label'     => 'shared/menu.report',
        'children'  =>
            [
                [
                    'label'     => 'general-ledger/menu.daily-cash',
                    'route'     => 'general-ledger/report/daily-cash',
                    'resource'  => 'Generalledger\Report\DailyCash',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'general-ledger/menu.general-journal',
                    'route'     => 'general-ledger/report/general-journal',
                    'resource'  => 'Generalledger\Report\JournalEntries',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'general-ledger/menu.account-post',
                    'route'     => 'general-ledger/report/account-post',
                    'resource'  => 'Generalledger\Report\AccountPost',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'general-ledger/menu.income',
                    'route'     => 'general-ledger/report/income',
                    'resource'  => 'Generalledger\Report\IncomeStatement',
                    'privilege' => 'view'
                ],
                [
                    'label'     => 'general-ledger/menu.trial-balance',
                    'route'     => 'general-ledger/report/trial-balance',
                    'resource'  => 'Generalledger\Report\BalanceSheet',
                    'privilege' => 'view'
                ],
            ]
        ],
    ]
];
