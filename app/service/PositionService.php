<?php

namespace app\service;

use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\repository\PositionRepository;
use DI\Attribute\Inject;

/**
 * class PositionService
 * @extends IService<PositionRepository>
 */
final class PositionService extends IService
{
    #[Inject]
    public PositionRepository $repository;

    public function batchDataPermission(int $id, array $policy): void
    {
        $entity = $this->repository->findById($id);
        if ($entity === null) {
            throw new UnprocessableEntityException(ResultCode::NOT_FOUND);
        }
        $policyEntity = $entity->policy()->first();
        if (empty($policyEntity)) {
            $entity->policy()->create($policy);
        } else {
            $policyEntity->update($policy);
        }
    }
}
