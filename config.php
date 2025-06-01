<?php

return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'laptop_market',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600 // 1 година
    ],
    'site' => [
        'name' => 'Laptop Market',
        'url' => 'http://localhost/laptop-market'
    ],
    'admin' => [
        'items_per_page' => 10
    ]
];