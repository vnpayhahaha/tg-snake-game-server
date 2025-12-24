<?php

namespace app\service;

use app\exception\BusinessException;
use app\lib\enum\ResultCode;
use app\model\ModelDepartment;
use app\repository\DepartmentRepository;
use DI\Attribute\Inject;
use support\Db;

/**
 * @extends IService<DepartmentRepository>
 */
class DepartmentService extends IService
{
    #[Inject]
    public DepartmentRepository $repository;

    public function create(array $data): mixed
    {
        return Db::transaction(function () use ($data) {
            $entity = $this->repository->create($data);
            $this->handleEntity($entity, $data);
            return $entity;
        });
    }

    protected function handleEntity(ModelDepartment $entity, array $data): void
    {
        if (isset($data['department_users'])) {
            $entity->department_users()->sync($data['department_users']);
        }
        if (isset($data['leader'])) {
            $entity->leader()->sync($data['leader']);
        }
    }


    public function updateById(mixed $id, array $data): mixed
    {
        return Db::transaction(function () use ($id, $data) {
            $entity = $this->repository->findById($id);
            if (empty($entity)) {
                throw new BusinessException(ResultCode::NOT_FOUND);
            }
            $this->handleEntity($entity, $data);
        });
    }

    public function getPositionsByDepartmentId(int $id): array
    {
        $entity = $this->repository->findById($id);
        if (empty($entity)) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }
        return $entity->positions()->get(['id', 'name'])->toArray();
    }
}
