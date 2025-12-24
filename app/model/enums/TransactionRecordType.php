<?php

namespace app\model\enums;

use app\constants\TransactionRecord;
use app\lib\traits\ConstantsTrait;

enum TransactionRecordType: int
{
    use ConstantsTrait;

    // 业务交易类型：10-订单交易 11-订单退款 20-人工加帐 21-人工减帐 23-冻结 24-解冻 30-收转付 31-付转收 40-冲正 41-调整差错
    case ORDER_TRANSACTION = TransactionRecord::TYPE_ORDER_TRANSACTION;
    case ORDER_REFUND = TransactionRecord::TYPE_ORDER_REFUND;
    case MANUAL_ADD = TransactionRecord::TYPE_MANUAL_ADD;
    case MANUAL_SUB = TransactionRecord::TYPE_MANUAL_SUB;
    case FREEZE = TransactionRecord::TYPE_FREEZE;
    case UNFREEZE = TransactionRecord::TYPE_UNFREEZE;
    case TRANSFER_RECEIVE_TO_PAY = TransactionRecord::TYPE_TRANSFER_RECEIVE_TO_PAY;
    case TRANSFER_PAY_TO_RECEIVE = TransactionRecord::TYPE_TRANSFER_PAY_TO_RECEIVE;
    case REVERSE = TransactionRecord::TYPE_REVERSE;
    case ERROR_ADJUST = TransactionRecord::TYPE_ERROR_ADJUST;
}
