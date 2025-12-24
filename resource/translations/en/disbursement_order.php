<?php

return [
    'enums' => [
        'status'        => [
            // 0-创建中  1-已创建 2-已分配  10-待支付 11-待回填 20-成功 30-挂起 \r\n    40-失败 41-已取消 43-已失效 44-冲正
            0  => 'Creating',
            1  => 'Created',
            2  => 'Allocated',
            10 => 'Pending payment',
            11 => 'Processing',
            20 => 'Success',
            30 => 'Suspend',
            40 => 'Failed',
            41 => 'Cancelled',
            43 => 'Invalid',
            44 => 'Adjusted to failure',
        ],
        'notify_status' => [
            'not_notify'     => 'Not Notified',
            'notify_success' => 'Notify Success',
            'notify_fail'    => 'Notify Fail',
            'callback_ing'   => 'In the callback',
        ],
        'payment_type'  => [
            'bank_card' => 'Bank Card',
            'upi'       => 'UPI',
        ],
        'channel_type'  => [
            'bank'     => 'Bank',
            'upstream' => 'Upstream',
        ],
        'bill_template' => [
            'icici'        => 'ICICI',
            'icici2'       => 'ICICI2',
            'bandhan'      => 'Bandhan',
            'federal'      => 'Federal',
            'yesmsme'      => 'YES MSME',
            'yesbusiness'  => 'YES Business',
            'axis'         => 'Axis',
            'axisneft'     => 'Axis NEFT',
            'axisneo'      => 'Axis NEO',
            'idfc'         => 'IDFC',
            'iobsamebank'  => 'IOB Same Bank',
            'iobotherbank' => 'IOB Other Bank',
        ]
    ],
];
