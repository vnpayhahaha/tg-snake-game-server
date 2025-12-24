<?php

return [
    'enums' => [
        'status'          => [
            0  => 'Created',
            10 => 'Pending payment',
            20 => 'Success',
            30 => 'Suspended',
            40 => 'Failed',
            41 => 'Cancelled',
            43 => 'Invalid',
            44 => 'Refunded',
        ],
        'collection_type' => [
            'bank_account' => 'Bank Account',
            'upi'          => 'UPI',
            'upstream'     => 'Upstream',
        ],
        'settlement_type' => [
            'not_settled'  => 'Not Settled',
            'paid_amount'  => 'Paid Amount',
            'order_amount' => 'Order Amount',
        ],
        'recon_type'      => [
            'not_recon'       => 'Not Recon',
            'auto_recon'      => 'Auto Recon',
            'manual_recon'    => 'Manual Recon',
            'interface_recon' => 'Interface Recon',
            'robot_recon'     => 'Robot Recon',
        ],
        'notify_status'   => [
            'not_notify'     => 'Not Notified',
            'notify_success' => 'Notify Success',
            'notify_fail'    => 'Notify Fail',
            'callback_ing'   => 'In the callback',
        ],
    ],
];
