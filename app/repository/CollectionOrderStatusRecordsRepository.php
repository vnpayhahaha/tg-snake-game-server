<?php

namespace app\repository;

use app\model\ModelCollectionOrderStatusRecords;
use DI\Attribute\Inject;

/**
 * Class CollectionOrderStatusRecordsRepository.
 * @extends IRepository<ModelCollectionOrderStatusRecords>
 */
final class CollectionOrderStatusRecordsRepository extends IRepository
{
    #[Inject]
    protected ModelCollectionOrderStatusRecords $model;
}