<?php

namespace app\service\handle;

use app\model\ModelBankDisbursementUpload;
use app\service\IService;

abstract class BankDisbursementBillAbstract  extends IService
{
    use BankDisbursementBillTrait;

    abstract public function importBill(ModelBankDisbursementUpload $model): bool;
}