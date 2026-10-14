<?php

return [
    /*
    | Technical roles keep the current boolean admin data compatible while
    | leaving room for the D04 business-role mapping in a later admin module.
    */
    'roles' => [
        'administrator' => ['*'],
        'partner' => [
            'catalog.view',
            'account.view',
            'account.manage',
            'order.create',
            'order.view-own',
        ],
    ],

    'aliases' => [
        'admin' => 'administrator',
    ],
];
