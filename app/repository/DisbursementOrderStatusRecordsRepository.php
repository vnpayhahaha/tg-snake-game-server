<?php

namespace app\repository;

use app\model\ModelDisbursementOrderStatusRecords;
use DI\Attribute\Inject;

/**
 * Class DisbursementOrderStatusRecordsRepository.
 * @extends IRepository<ModelDisbursementOrderStatusRecords>
 */
final class DisbursementOrderStatusRecordsRepository extends IRepository
{
    #[Inject]
    protected ModelDisbursementOrderStatusRecords $model;
}