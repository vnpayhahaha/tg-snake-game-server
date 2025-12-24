<?php

namespace app\model\enums;

use app\constants\DisbursementOrderVerificationQueue;

enum DisbursementOrderVerificationQueuePaymentStatus: int
{
    // 支付状态:0未支付1支付中2支付成功3支付失败
    case NOT_PAY = DisbursementOrderVerificationQueue::PAY_STATUS_NOT_PAY;
    case PAYING = DisbursementOrderVerificationQueue::PAY_STATUS_PAYING;
    case SUCCESS = DisbursementOrderVerificationQueue::PAY_STATUS_SUCCESS;
    case FAIL = DisbursementOrderVerificationQueue::PAY_STATUS_FAIL;
}