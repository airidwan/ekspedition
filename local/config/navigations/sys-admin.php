<?php

return [
    'label' => 'sys-admin/menu.sys-admin',
    'icon' => 'laptop',
    'children' =>
    [
        [
            'label' => 'shared/menu.master',
            'children' =>
            [
                [
                    'label' => 'sys-admin/menu.user',
                    'route' => 'sys-admin/master/user',
                    'resource' => 'SysAdmin\Master\User',
                    'privilege' => 'view',
                ],
                [
                    'label' => 'sys-admin/menu.role',
                    'route' => 'sys-admin/master/role',
                    'resource' => 'SysAdmin\Master\Role',
                    'privilege' => 'view',
                ],
            ]
        ]
    ]
];
