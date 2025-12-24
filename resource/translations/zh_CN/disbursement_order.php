<?php

return [
    'enums' => [
        'status'        => [
            // 0-创建中 1-已创建 2-已分配 10-待支付 11-待回填 20-成功 30-挂起 \r\n    40-失败 41-已取消 43-已失效 44-已退款
            0  => '创建中',
            1  => '已创建',
            2  => '已分配',
            10 => '待支付',
            11 => '支付中',
            20 => '已支付',
            30 => '已挂起',
            40 => '已失败',
            41 => '已取消',
            43 => '已失效',
            44 => '冲正',
        ],
        'notify_status' => [
            'not_notify'     => '未通知',
            'notify_success' => '已通知',
            'notify_fail'    => '通知失败',
            'callback_ing'   => '回调中',
        ],
        'payment_type'  => [
            'bank_card' => '银行卡',
            'upi'       => 'UPI',
        ],
        'channel_type'  => [
            'bank'     => '银行',
            'upstream' => '上游',
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
