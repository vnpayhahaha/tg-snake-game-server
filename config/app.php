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

use support\Request;

return [
    'debug'             => env('APP_DEBUG', true),
    'error_reporting'   => E_ALL,
    'default_timezone'  => 'Asia/Shanghai',
    'request_class'     => Request::class,
    'public_path'       => base_path() . DIRECTORY_SEPARATOR . 'public',
    'runtime_path'      => base_path(false) . DIRECTORY_SEPARATOR . 'runtime',
    'controller_suffix' => 'Controller',
    'controller_reuse'  => false,
    'enable'            => true,
    'cash_desk_url'     => env('CASH_DESK_URL', ''),

    /**
     * Telegram管理员白名单（应急方案）
     *
     * 用途：当Bot没有管理员权限时，可以通过白名单临时授予用户管理员权限
     *
     * 使用方法：
     * 1. 在群组中执行 /get_id 获取您的Telegram用户ID
     * 2. 将用户ID添加到下面的数组中
     * 3. 重启服务：php start.php restart
     *
     * 示例：
     * 'telegram_admin_whitelist' => [123456789, 987654321],
     *
     * 注意：这只是临时方案，建议尽快将Bot设置为群组管理员
     */
    'telegram_admin_whitelist' => env('TELEGRAM_ADMIN_WHITELIST', []),
];
