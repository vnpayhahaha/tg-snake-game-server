<?php

namespace app\repository;

use app\model\ModelDisbursementOrderVerificationQueue;
use DI\Attribute\Inject;

class DisbursementOrderVerificationQueueRepository extends IRepository
{
    #[Inject]
    protected ModelDisbursementOrderVerificationQueue $model;
}