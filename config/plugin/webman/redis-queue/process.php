<?php
return [
    'notice-consumer'  => [
        'handler'     => Webman\RedisQueue\Process\Consumer::class,
        'count'       => 2, // 可以设置多进程同时消费
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis/Notice'
        ]
    ],
    'transaction-consumer'  => [
        'handler'     => Webman\RedisQueue\Process\Consumer::class,
        'count'       => 1,
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis/Transaction'
        ]
    ],
    'synchronize-consumer'  => [
        'handler'     => Webman\RedisQueue\Process\Consumer::class,
        'count'       => 2, // 可以设置多进程同时消费
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis/Synchronize'
        ]
    ],
    'queue-consumer-failed'  => [
        'handler'     => app\queue\redis\failed\FailedConsumer::class,
        'count'       => 1, // 可以设置多进程同时消费
    ]
];
