<?php

namespace app\repository;

use app\constants\TgGameGroupConfig as ConfigConst;
use app\model\ModelTgGameGroupConfig;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TgGameGroupConfigRepository.
 * @extends IRepository<ModelTgGameGroupConfig>
 */
class TgGameGroupConfigRepository extends IRepository
{
    #[Inject]
    protected ModelTgGameGroupConfig $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['tg_chat_id']) && filled($params['tg_chat_id'])) {
            $query->where('tg_chat_id', $params['tg_chat_id']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['wallet_change_status']) && filled($params['wallet_change_status'])) {
            $query->where('wallet_change_status', $params['wallet_change_status']);
        }

        return $query;
    }

    /**
     * 根据Telegram群组ID查询配置
     */
    public function getByTgChatId(int $tgChatId): ?ModelTgGameGroupConfig
    {
        return $this->model::query()
            ->where('tg_chat_id', $tgChatId)
            ->first();
    }

    /**
     * 根据租户ID查询配置列表
     */
    public function getByTenantId(string $tenantId): \Illuminate\Support\Collection
    {
        return $this->model::query()
            ->where('tenant_id', $tenantId)
            ->get();
    }

    /**
     * 获取所有活跃配置
     */
    public function getActiveConfigs(): \Illuminate\Support\Collection
    {
        return $this->model::query()
            ->where('status', ConfigConst::STATUS_ENABLED)
            ->get();
    }

    /**
     * 获取所有正在变更中的配置
     */
    public function getChangingConfigs(): \Illuminate\Support\Collection
    {
        return $this->model::query()
            ->where('wallet_change_status', ConfigConst::WALLET_CHANGE_STATUS_CHANGING)
            ->get();
    }

    /**
     * 检查钱包变更状态
     */
    public function checkWalletChangeStatus(int $id): int
    {
        $config = $this->model::query()
            ->whereKey($id)
            ->first(['wallet_change_status']);

        return $config ? $config->wallet_change_status : 1;
    }

    /**
     * 开始钱包变更
     */
    public function startWalletChange(int $id, string $newAddress, \Carbon\Carbon $startAt, \Carbon\Carbon $endAt): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'pending_wallet_address' => $newAddress,
                'wallet_change_status' => ConfigConst::WALLET_CHANGE_STATUS_CHANGING,
                'wallet_change_start_at' => $startAt,
                'wallet_change_end_at' => $endAt,
            ]);
    }

    /**
     * 取消钱包变更
     */
    public function cancelWalletChange(int $id): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'pending_wallet_address' => null,
                'wallet_change_status' => ConfigConst::WALLET_CHANGE_STATUS_NORMAL,
                'wallet_change_start_at' => null,
                'wallet_change_end_at' => null,
            ]);
    }

    /**
     * 完成钱包变更
     */
    public function completeWalletChange(int $id, string $newAddress, int $newWalletCycle): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'wallet_address' => $newAddress,
                'wallet_change_count' => $newWalletCycle,
                'pending_wallet_address' => null,
                'wallet_change_status' => ConfigConst::WALLET_CHANGE_STATUS_NORMAL,
                'wallet_change_start_at' => null,
                'wallet_change_end_at' => null,
            ]);
    }

    /**
     * 获取租户下所有群组配置（带分页）
     */
    public function getByTenantIdPaginated(string $tenantId, array $params = [], int $page = 1, int $pageSize = 10): array
    {
        $query = $this->model::query()
            ->where('tenant_id', $tenantId)
            ->with('group');

        // 支持状态筛选
        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        // 支持群组名称搜索
        if (isset($params['tg_chat_title']) && filled($params['tg_chat_title'])) {
            $query->where('tg_chat_title', 'like', '%' . $params['tg_chat_title'] . '%');
        }

        $total = $query->count();
        $list = $query->orderByDesc('created_at')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'total' => $total,
            'list' => $list,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取租户下群组统计概览
     */
    public function getTenantGroupStats(string $tenantId): array
    {
        $query = $this->model::query()->where('tenant_id', $tenantId);

        return [
            'total_groups' => (clone $query)->count(),
            'active_groups' => (clone $query)->where('status', ConfigConst::STATUS_ENABLED)->count(),
            'inactive_groups' => (clone $query)->where('status', ConfigConst::STATUS_DISABLED)->count(),
        ];
    }

    /**
     * 验证配置是否属于指定租户
     */
    public function belongsToTenant(int $configId, string $tenantId): bool
    {
        return $this->model::query()
            ->where('id', $configId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    /**
     * 获取租户下所有群组ID列表
     */
    public function getGroupIdsByTenantId(string $tenantId): array
    {
        return $this->model::query()
            ->where('tenant_id', $tenantId)
            ->with('group')
            ->get()
            ->pluck('group.id')
            ->filter()
            ->toArray();
    }
}
