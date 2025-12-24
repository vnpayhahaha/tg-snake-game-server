<?php
return [
    'default'     => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'    => env('DB_DRIVER', 'mysql'),
            'host'      => env('DB_HOST', 'localhost'),
            'port'      => env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE', 'webman'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix'    => env('DB_PREFIX', ''),
            'strict'    => true,
            'engine'    => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => true, // Must be false for Swoole and Swow drivers.
            ],
            'pool'      => [
                'max_connections'    => 20,
                'min_connections'    => 1,
                'wait_timeout'       => 3,
                'idle_timeout'       => 60,
                'heartbeat_interval' => 50,
            ],
        ],
    ],
];
