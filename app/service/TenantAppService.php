<?php

namespace app\service;

use app\exception\BusinessException;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\repository\TenantAppRepository;
use app\repository\TenantRepository;
use DI\Attribute\Inject;
use Exception;

/**
 * @extends IService<TenantAppRepository>
 */
final class TenantAppService extends IService
{
    #[Inject]
    protected TenantAppRepository $repository;

    #[Inject]
    protected TenantRepository $tenantRepository;

    /**
     * 生成新的app key.
     * @throws Exception
     */
    public function generateAppKey(): string
    {
        return bin2hex(random_bytes(5));
    }

    /**
     * 生成新的app secret.
     * @throws Exception
     */
    public function generateAppSecret(): string
    {
        return base64_encode(bin2hex(random_bytes(32)));
    }

    // 获取 AppSecret by app_key
    public function getAppSecretByAppKey(string $app_key): string|null
    {
        return $this->repository->getAppSecretByAppKey($app_key);
    }


    // 获取 App信息 by app_key
    public function queryByAppKey(string $app_key): mixed
    {
        return $this->repository->getQuery()->where('app_key', $app_key)->first();
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
        if ($tenant->app_num_limit > -1 && $countUser >= $tenant->app_num_limit) {
            throw new BusinessException(ResultCode::USER_NUM_LIMIT_EXCEEDED);
        }
        return parent::create($data);
    }
}
