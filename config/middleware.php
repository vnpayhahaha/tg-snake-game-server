<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */


return [
    ''        => [
        app\middleware\CorsMiddleware::class,
        app\middleware\RequestIdMiddleware::class,
        app\middleware\LangMiddleware::class,
    ],
    'backend' => [
        app\middleware\AccessTokenMiddleware::class,
        app\middleware\PermissionMiddleware::class,
        app\middleware\OperationLogMiddleware::class,
    ],
    'tenant'  => [
        app\middleware\AccessTokenMiddleware::class,
    ],
    'openapi' => [
        app\middleware\OpenApiSignatureMiddleware::class,
        app\middleware\OpenApiLogMiddleware::class,
        app\middleware\OpenApiRateLimitMiddleware::class,
    ],
];
