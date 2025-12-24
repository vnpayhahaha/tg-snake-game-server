<?php

return [
    'enums' => [
        'status'          => [
            // 0-创建 10-处理中 20-成功 30-挂起 40-失败 41-已取消 43-已失效 44-已退款
            0  => '已创建',
            10 => '待支付',
            20 => '已支付',
            30 => '已挂起',
            40 => '已失败',
            41 => '已取消',
            43 => '已失效',
            44 => '已退款',
        ],
        'collection_type' => [
            'bank_account' => '银行卡',
            'upi'          => 'UPI',
            'upstream'     => '上游',
        ],
        'settlement_type' => [
            'not_settled'  => '未入账',
            'paid_amount'  => '实付金额',
            'order_amount' => '订单金额',
        ],
        'recon_type'      => [
            'not_recon'       => '未核销',
            'auto_recon'      => '自动核销',
            'manual_recon'    => '人工核销',
            'interface_recon' => '接口核销',
            'robot_recon'     => '机器人核销',
        ],
        'notify_status'   => [
            'not_notify'     => '未通知',
            'notify_success' => '已通知',
            'notify_fail'    => '通知失败',
            'callback_ing'   => '回调中',
        ],
    ],
];
