<?php

namespace app\service;

use app\repository\LeaderRepository;
use DI\Attribute\Inject;
use support\Log;

/**
 * class LeaderService
 * @extends IService<LeaderRepository>
 */
final class LeaderService extends IService
{
    #[Inject]
    public LeaderRepository $repository;


    public function deleteByDoubleKey(array $data): bool
    {
        try {
            $this->repository->deleteByDoubleKey($data['dept_id'], $data['user_ids']);
            return true;
        } catch (\Exception $e) {
            Log::warning('LeaderService=》deleteByDoubleKey error：'.$e->getMessage());
            return false;
        }
    }

}
