<?php

use app\service\upload\storage\Cos;
use app\service\upload\storage\Local;
use app\service\upload\storage\Oss;
use app\service\upload\storage\Qiniu;
use app\service\upload\storage\S3;

return [
    'debug'           => config('app.debug'),
    'adapter_classes' => [
        'local' => Local::class,
        'oss'   => Oss::class,
        'cos'   => Cos::class,
        'qiniu' => Qiniu::class,
        's3'    => S3::class,
    ],
    'config'          => [
        'local' => [
            'root'    => 'public',
            'dirname' => 'upload',
            'domain'  => env('APP_DOMAIN', 'http://127.0.0.1:9501'),
        ],
        'oss'   => [
            'accessKeyId'     => '',
            'accessKeySecret' => '',
            'bucket'          => '',
            'domain'          => '',
            'endpoint'        => '',
            'dirname'         => '',
            'remark'          => '',
        ],
        'cos'   => [
            'secretId'  => '',
            'secretKey' => '',
            'bucket'    => '',
            'domain'    => '',
            'region'    => '',
            'dirname'   => '',
            'remark'    => '',
        ],
        'qiniu' => [
            'accessKey' => '',
            'secretKey' => '',
            'bucket'    => '',
            'domain'    => '',
            'dirname'   => '',
            'region'    => '',
            'remark'    => '',
        ],
        's3'    => [
            'key'      => '',
            'secret'   => '',
            'region'   => '',
            'bucket'   => '',
            'domain'   => '',
            'dirname'  => '',
            'remark'   => '',
            'endpoint' => '',
            'acl'      => ''
        ],
    ],

];
