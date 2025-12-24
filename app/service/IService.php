<?php

namespace app\service;

use app\repository\IRepository;
use Illuminate\Support\Collection;

/**
 * @template T of Model
 * @property IRepository<T> $repository
 */
abstract class IService
{
    public function count(array $params): int
    {
        return $this->repository->count($params);
    }

    public function page(array $params, int $page = 1, int $pageSize = 10): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    public function getList(array $paras): Collection
    {
        return $this->repository->list($paras);
    }

    /**
     * @return T
     */
    public function create(array $data): mixed
    {
        return $this->repository->create($data);
    }

    /**
     * @return T
     */
    public function save(array $data): mixed
    {
        return $this->create($data);
    }

    public function updateById(mixed $id, array $data): mixed
    {
        return $this->repository->updateById($id, $data);
    }

    public function deleteById(mixed $id): int
    {
        return $this->repository->deleteById($id);
    }

    /**
     * @return null|T
     */
    public function findById(mixed $id): mixed
    {
        return $this->repository->findById($id);
    }

    public function existsById(mixed $id): bool
    {
        return $this->repository->existsById($id);
    }

    /**
     * 单个或批量真实删除数据.
     */
    public function realDelete(array $ids): bool
    {
        return ! empty($ids) && $this->repository->realDelete($ids);
    }

    /**
     * 单个或批量从回收站恢复数据.
     */
    public function recovery(array $ids): bool
    {
        return ! empty($ids) && $this->repository->recovery($ids);
    }

    /**
     * 单个或批量禁用数据.
     */
    public function disable(array $ids, string $field = 'status'): bool
    {
        return ! empty($ids) && $this->repository->disable($ids, $field);
    }

    /**
     * 单个或批量启用数据.
     */
    public function enable(array $ids, string $field = 'status'): bool
    {
        return ! empty($ids) && $this->repository->enable($ids, $field);
    }

}
