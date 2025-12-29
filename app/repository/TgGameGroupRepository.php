<?php

namespace app\repository;

use app\model\ModelTgGameGroup;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TgGameGroupRepository.
 * @extends IRepository<ModelTgGameGroup>
 */
class TgGameGroupRepository extends IRepository
{
    #[Inject]
    protected ModelTgGameGroup $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['config_id']) && filled($params['config_id'])) {
            $query->where('config_id', $params['config_id']);
        }

        if (isset($params['tg_chat_id']) && filled($params['tg_chat_id'])) {
            $query->where('tg_chat_id', $params['tg_chat_id']);
        }

        return $query;
    }

    /**
     * 根据配置ID查询游戏群组
     */
    public function getByConfigId(int $configId): ?ModelTgGameGroup
    {
        return $this->model::query()
            ->where('config_id', $configId)
            ->first();
    }

    /**
     * 根据Telegram群组ID查询
     */
    public function getByTgChatId(int $tgChatId): ?ModelTgGameGroup
    {
        return $this->model::query()
            ->where('tg_chat_id', $tgChatId)
            ->first();
    }

    /**
     * 更新奖池金额
     */
    public function updatePrizePool(int $id, float $amount): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'prize_pool_amount'   => $amount,
                'current_snake_nodes' => ''
            ]);
    }

    /**
     * 更新蛇身节点
     */
    public function updateSnakeNodes(int $id, string $currentNodes, string $lastNodes = null): bool
    {
        $data = ['current_snake_nodes' => $currentNodes];

        if ($lastNodes !== null) {
            $data['last_snake_nodes'] = $lastNodes;
        }

        return (bool)$this->model::query()
            ->whereKey($id)
            ->update($data);
    }

    /**
     * 更新最后中奖信息
     */
    public function updateLastPrize(int $id, array $prizeData): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'last_prize_nodes'     => $prizeData['nodes'] ?? '',
                'last_prize_amount'    => $prizeData['amount'] ?? 0,
                'last_prize_address'   => $prizeData['address'] ?? '',
                'last_prize_serial_no' => $prizeData['serial_no'] ?? '',
                'last_prize_at'        => $prizeData['prize_at'] ?? now(),
            ]);
    }

    /**
     * 使用乐观锁更新版本号
     */
    public function updateWithVersion(int $id, array $data, int $currentVersion): bool
    {
        $data['version'] = $currentVersion + 1;

        $affected = $this->model::query()
            ->where('id', $id)
            ->where('version', $currentVersion)
            ->update($data);

        return $affected > 0;
    }

    /**
     * 获取群组统计信息
     */
    public function getGroupStatistics(int $groupId = null): array
    {
        $query = $this->model::query();

        if ($groupId) {
            $query->where('id', $groupId);
        }

        return [
            'total_groups'     => (clone $query)->count(),
            'total_prize_pool' => (clone $query)->sum('prize_pool_amount'),
            'avg_prize_pool'   => (clone $query)->avg('prize_pool_amount'),
        ];
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)->with('config:id,tenant_id,tg_chat_title,status')->paginate(
            perPage: $pageSize,
            pageName: static::PER_PAGE_PARAM_NAME,
            page: $page,
        );
        return $this->handlePage($result);
    }
}
