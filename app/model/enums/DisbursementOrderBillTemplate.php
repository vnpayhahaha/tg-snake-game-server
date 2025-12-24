<?php
namespace app\model\enums;
use app\constants\DisbursementOrder;
use app\lib\traits\ConstantsTrait;

enum DisbursementOrderBillTemplate: string
{
    use ConstantsTrait;

    case ICICI = DisbursementOrder::BILL_TEMPLATE_ICICI;
    case ICICI2 = DisbursementOrder::BILL_TEMPLATE_ICICI2;
    case BANDHAN = DisbursementOrder::BILL_TEMPLATE_BANDHAN;
    case YES_MSME = DisbursementOrder::BILL_TEMPLATE_YES_MSME;
    case AXIS = DisbursementOrder::BILL_TEMPLATE_AXIS;
    case AXIS_NEFT = DisbursementOrder::BILL_TEMPLATE_AXIS_NEFT;
    case AXIS_NEO = DisbursementOrder::BILL_TEMPLATE_AXIS_NEO;
    case IDFC = DisbursementOrder::BILL_TEMPLATE_IDFC;
    case IOB_SAME = DisbursementOrder::BILL_TEMPLATE_IOB_SAME;
    case IOB_OTHER = DisbursementOrder::BILL_TEMPLATE_IOB_OTHER;

}