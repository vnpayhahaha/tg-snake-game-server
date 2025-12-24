<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
return [
    'id '                    => ' User ID, primary key ',
    'username '              => ' Username ',
    'user_type '             => ' User type: (100 system users) ',
    'nickname '              => ' user nickname ',
    'phone '                 => ' Phone ',
    'email '                 => ' User Email ',
    'avatar '                => ' user avatar ',
    'signed '                => ' Personal signature ',
    'dashboard '             => ' Backstage Home Type ',
    'Status'                 => ' Status',
    'login_ip '              => ' Last login IP ',
    'login_time '            => ' Last login time ',
    'backend_setting'        => ' Background settings data',
    'created_by '            => ' Creator ',
    'updated_by '            => ' Updater ',
    'created_at'             => ' Creation time',
    'updated_at '            => ' Update time ',
    'remark '                => ' Remark ',
    'username_exist'         => 'Username already exists',
    'enums'                  => [
        'type '  => [
            100 => 'System user',
            200 => 'Normal user',
        ],
        'status' => [
            1 => 'Active',
            2 => 'Disabled',
        ],
    ],
    'disable'                => 'Account deactivated',
    'old_password_error'     => 'Old password error',
    'old_password '          => ' Old password ',
    'password_confirmation ' => ' Confirm password ',
    'password'               => ' Password ',
];
