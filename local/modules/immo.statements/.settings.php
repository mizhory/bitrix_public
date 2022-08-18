<?php

use Immo\Statements\Access\User;

return [
    'services' => [
        'value' => [
            'immo:access.user' => [
                'className' => User::class
            ]
        ],
        'readonly' => true
    ]
];