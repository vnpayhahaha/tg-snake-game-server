<?php

namespace app\service;

use app\exception\BusinessException;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\repository\TenantRepository;
use app\repository\TenantUserRepository;
use DI\Attribute\Inject;

/**
 * @extends IService<TenantUserRepository>
 */
final class TenantUserService extends IService
{
    #[Inject]
    public TenantUserRepository $repository;

    #[Inject]
    protected TenantRepository $tenantRepository;

    public function resetPassword(?int $id): bool
    {
        if ($id === null) {
            return false;
        }
        $entity = $this->repository->findById($id);
        if ($entity === null) {
            throw new UnprocessableEntityException(ResultCode::USER_NOT_EXIST);
        }
        $entity->resetPassword();
        return $entity->save();
    }

    public function create(array $data): mixed
    {
        // 读取租户 ID，判断 user_num_limit 限制
        $tenantId = $data['tenant_id'] ?? null;
        if (!$tenantId) {
            throw new UnprocessableEntityException(ResultCode::NOT_FOUND);
        }
        $tenant = $this->tenantRepository->getQuery()->where('tenant_id', $tenantId)->firstOrFail();
        $countUser = $this->repository->getQuery()->where('tenant_id', $tenantId)->count();
        if ($tenant->user_num_limit > -1 && $countUser >= $tenant->user_num_limit) {
            throw new BusinessException(ResultCode::USER_NUM_LIMIT_EXCEEDED);
        }
        return parent::create($data);
    }
}
