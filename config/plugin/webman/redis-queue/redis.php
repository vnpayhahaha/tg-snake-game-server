<?php
return [
    'default' => [
        'host'    => 'redis://' . env('REDIS_HOST', 'localhost') . ':' . env('REDIS_PORT', 6379),
        'options' => [
            'auth'          => env('REDIS_AUTH', null),
            'db'            => env('REDIS_QUEUE_DB', 1),
            'prefix'        => env('APP_NAME', ''),
            'max_attempts'  => env('REDIS_QUEUE_MAX_ATTEMPTS', 5),
            'retry_seconds' => env('REDIS_QUEUE_RETRY_SECONDS', 10),
        ],
        // Connection pool, supports only Swoole or Swow drivers.
        'pool'    => [
            'max_connections'    => 5,
            'min_connections'    => 1,
            'wait_timeout'       => 10,
            'idle_timeout'       => 60,
            'heartbeat_interval' => 50,
        ]
    ],
    'synchronize' => [
        'host'    => 'redis://' . env('REDIS_HOST', 'localhost') . ':' . env('REDIS_PORT', 6379),
        'options' => [
            'auth'          => env('REDIS_AUTH', null),
            'db'            => env('REDIS_QUEUE_DB_SYNC', 2),
            'prefix'        => '',
            'max_attempts'  => env('REDIS_QUEUE_MAX_ATTEMPTS', 5),
            'retry_seconds' => env('REDIS_QUEUE_RETRY_SECONDS', 10),
        ],
        'pool'    => [
            'max_connections'    => 5,
            'min_connections'    => 1,
            'wait_timeout'       => 10,
            'idle_timeout'       => 60,
            'heartbeat_interval' => 50,
        ]
    ],
];
