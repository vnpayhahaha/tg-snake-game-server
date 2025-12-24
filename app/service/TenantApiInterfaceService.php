<?php

namespace app\service;

use app\lib\annotation\Cacheable;
use app\repository\TenantApiInterfaceRepository;

final class TenantApiInterfaceService extends BaseService
{

    public TenantApiInterfaceRepository $repository;

    public function __construct(TenantApiInterfaceRepository $repository)
    {
        $this->repository = $repository;
    }
    #[Cacheable(
        prefix: 'openapi:reatlimit',
        value: '_#{api_name}',
        ttl: 60,
        group: 'redis'
    )]
    private function getRateLimitByApiName(string $api_name): int
    {
        return (int)$this->repository->getQuery()->where('api_name', $api_name)->value('rate_limit') ?? 0;
    }

    #[Cacheable(
        prefix: 'openapi:reatlimit',
        value: '_#{api_uri}',
        ttl: 60,
        group: 'redis'
    )]
    private function getRateLimitByApiUri(string $api_uri): int
    {
        return (int)$this->repository->getQuery()->where('api_uri', $api_uri)->value('rate_limit') ?? 0;
    }

}
