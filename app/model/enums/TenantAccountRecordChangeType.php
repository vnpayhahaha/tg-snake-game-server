<?php

namespace app\model\enums;

use app\constants\TenantAccountRecord;

enum TenantAccountRecordChangeType: int
{
    // 变更类型：1-订单交易 2-订单退款 3-人工加帐 4-人工减帐 5-冻结 6-解冻 7-转入 8-转出 9-冲正 10-调整差错
    case CHANGE_TYPE_TRANSACTION = TenantAccountRecord::CHANGE_TYPE_TRANSACTION;
    case CHANGE_TYPE_REFUND = TenantAccountRecord::CHANGE_TYPE_REFUND;
    case CHANGE_TYPE_MANUAL_ADD = TenantAccountRecord::CHANGE_TYPE_MANUAL_ADD;
    case CHANGE_TYPE_MANUAL_SUB = TenantAccountRecord::CHANGE_TYPE_MANUAL_SUB;
    case CHANGE_TYPE_FREEZE = TenantAccountRecord::CHANGE_TYPE_FREEZE;
    case CHANGE_TYPE_UNFREEZE = TenantAccountRecord::CHANGE_TYPE_UNFREEZE;
    case CHANGE_TYPE_TRANSFER_IN = TenantAccountRecord::CHANGE_TYPE_TRANSFER_IN;
    case CHANGE_TYPE_TRANSFER_OUT = TenantAccountRecord::CHANGE_TYPE_TRANSFER_OUT;
    case CHANGE_TYPE_REVERSE = TenantAccountRecord::CHANGE_TYPE_REVERSE;
    case CHANGE_TYPE_ERROR_ADJUST = TenantAccountRecord::CHANGE_TYPE_ERROR_ADJUST;

}
