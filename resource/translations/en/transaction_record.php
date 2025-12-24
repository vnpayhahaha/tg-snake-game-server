<?php
return [
    'enums' => [
        'type'                  => [
            'order_transaction'       => 'order transaction',
            'order_refund'            => 'order refund',
            'manual_add'              => 'manual add',
            'manual_sub'              => 'manual sub',
            'freeze'                  => 'freeze',
            'unfreeze'                => 'unfreeze',
            'transfer_receive_to_pay' => 'transfer receive to pay',
            'transfer_pay_to_receive' => 'transfer pay to receive',
            'reverse'                 => 'reverse',
            'error_adjust'            => 'error adjust',
        ],
        'status'                => [
            'waiting_settlement' => 'waiting settlement',
            'processing'         => 'processing',
            'cancel'             => 'cancel',
            'success'            => 'success',
            'fail'               => 'fail',
        ],
        'settlement_delay_mode' => [
            'd0'    => 'D0(Immediately)',
            'day'   => 'D(Natural day)',
            'trade' => 'T(Working day)',
        ],
        'holiday_adjustment'    => [
            'none'     => 'none',
            'postpone' => 'postpone',
            'advance'  => 'advance',
        ],
    ],
];
