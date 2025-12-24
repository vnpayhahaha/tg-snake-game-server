<?php

namespace app\service;

use app\exception\BusinessException;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\model\enums\RecycleBinEnabled;
use app\repository\RecycleBinRepository;
use DI\Attribute\Inject;
use support\Db;

/**
 * class PositionService
 * @extends IService<RecycleBinRepository>
 */
final class RecycleBinService extends IService
{
    #[Inject]
    public RecycleBinRepository $repository;

    /**
     * 恢复删除数据
     * @param int $id
     * @return mixed
     */
    public function restoreRecycleBin(int $id): mixed
    {
        $entity = $this->repository->findById($id);
        if (empty($entity)) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }
        return Db::transaction(function () use ($entity) {
            $tableName = $entity->table_name;
            $tableData = json_decode($entity->data, true);
            $columns = $this->getTableColumns($tableName);
            $tableData = array_intersect_key($tableData, array_flip($columns));
            Db::table($tableName)->insert($tableData);
            $entity->is_restored = RecycleBinEnabled::Restored;
            $entity->save();
        });

    }

    private function getTableColumns(string $tableName): array
    {
        return Db::getSchemaBuilder()->getColumnListing($tableName);
    }
}
