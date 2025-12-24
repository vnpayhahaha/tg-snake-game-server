<?php
return [
    'enums' => [
        'type'                  => [
            'order_transaction'       => '订单交易',
            'order_refund'            => '订单退款',
            'manual_add'              => '人工加帐',
            'manual_sub'              => '人工减帐',
            'freeze'                  => '冻结',
            'unfreeze'                => '解冻',
            'transfer_receive_to_pay' => '收转付',
            'transfer_pay_to_receive' => '付转收',
            'reverse'                 => '冲正',
            'error_adjust'            => '调整差错',
        ],
        'status'                => [
            'waiting_settlement' => '待结算',
            'processing'         => '结算中',
            'cancel'             => '撤销结算',
            'success'            => '已结算',
            'fail'               => '结算失败',
        ],
        'settlement_delay_mode' => [
            'd0'    => 'D0(立即)',
            'day'   => 'D(自然日)',
            'trade' => 'T(工作日)',
        ],
        'holiday_adjustment'    => [
            'none'     => '不调整',
            'postpone' => '顺延',
            'advance'  => '提前',
        ],
    ],
];
