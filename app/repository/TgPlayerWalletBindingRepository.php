<?php

namespace app\repository;

use app\model\ModelTgPlayerWalletBinding;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgPlayerWalletBindingRepository.
 * @extends IRepository<ModelTgPlayerWalletBinding>
 */
class TgPlayerWalletBindingRepository extends IRepository
{
    #[Inject]
    protected ModelTgPlayerWalletBinding $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['group_id']) && filled($params['group_id'])) {
            $query->where('group_id', $params['group_id']);
        }

        if (isset($params['tg_user_id']) && filled($params['tg_user_id'])) {
            $query->where('tg_user_id', $params['tg_user_id']);
        }

        if (isset($params['tg_username']) && filled($params['tg_username'])) {
            $query->where('tg_username', 'like', '%' . $params['tg_username'] . '%');
        }

        if (isset($params['wallet_address']) && filled($params['wallet_address'])) {
            $query->where('wallet_address', $params['wallet_address']);
        }

        return $query;
    }

    /**
     * 根据Telegram用户ID和群组ID查询绑定
     */
    public function getByTgUserId(int $groupId, int $tgUserId): ?ModelTgPlayerWalletBinding
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('tg_user_id', $tgUserId)
            ->first();
    }

    /**
     * 根据钱包地址查询绑定（同一个地址可能在多个群绑定）
     */
    public function getByWalletAddress(string $walletAddress, int $groupId = null): Collection
    {
        $query = $this->model::query()
            ->where('wallet_address', $walletAddress);

        if ($groupId !== null) {
            $query->where('group_id', $groupId);
        }

        return $query->get();
    }

    /**
     * 根据用户名查询绑定
     */
    public function getByUsername(int $groupId, string $username): ?ModelTgPlayerWalletBinding
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('tg_username', $username)
            ->first();
    }

    /**
     * 创建或更新绑定
     */
    public function createOrUpdate(array $data): ModelTgPlayerWalletBinding
    {
        $binding = $this->getByTgUserId($data['group_id'], $data['tg_user_id']);

        if ($binding) {
            $binding->update($data);
            return $binding;
        }

        return $this->model::query()->create($data);
    }

    /**
     * 更新钱包地址
     */
    public function updateWalletAddress(int $id, string $walletAddress): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update(['wallet_address' => $walletAddress]);
    }

    /**
     * 删除绑定
     */
    public function deleteBinding(int $groupId, int $tgUserId): bool
    {
        return (bool)$this->model::query()
            ->where('group_id', $groupId)
            ->where('tg_user_id', $tgUserId)
            ->delete();
    }

    /**
     * 获取群组的所有绑定
     */
    public function getGroupBindings(int $groupId): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->orderByDesc('bind_at')
            ->get();
    }

    /**
     * 统计群组绑定数量
     */
    public function countGroupBindings(int $groupId): int
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->count();
    }

    /**
     * 检查钱包地址在群组中是否已被绑定
     */
    public function isWalletBoundInGroup(int $groupId, string $walletAddress, int $excludeUserId = null): bool
    {
        $query = $this->model::query()
            ->where('group_id', $groupId)
            ->where('wallet_address', $walletAddress);

        if ($excludeUserId !== null) {
            $query->where('tg_user_id', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * 通过钱包地址反查Telegram用户信息
     */
    public function getUserByWalletAddress(int $groupId, string $walletAddress): ?ModelTgPlayerWalletBinding
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('wallet_address', $walletAddress)
            ->first();
    }

    /**
     * 批量获取用户绑定
     */
    public function getBatchByUserIds(int $groupId, array $userIds): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->whereIn('tg_user_id', $userIds)
            ->get();
    }

    /**
     * 获取最近绑定的用户
     */
    public function getRecentBindings(int $groupId, int $limit = 10): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->orderByDesc('bind_at')
            ->limit($limit)
            ->get();
    }
}
