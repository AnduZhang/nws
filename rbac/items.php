<?php
return [
    'login' => [
        'type' => 2,
    ],
    'logout' => [
        'type' => 2,
    ],
    'index' => [
        'type' => 2,
    ],
    'confirm' => [
        'type' => 2,
    ],
    'about' => [
        'type' => 2,
    ],
    'guest' => [
        'type' => 1,
        'description' => 'Guest',
        'ruleName' => 'userGroup',
        'children' => [
            'login',
            'index',
            'confirm',
        ],
    ],
    'registered' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'logout',
            'about',
            'guest',
        ],
    ],
];
