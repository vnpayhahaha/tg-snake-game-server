<?php

namespace app\service;

use app\repository\TgGameGroupRepository;
use app\repository\TgGameGroupConfigRepository;
use DI\Attribute\Inject;
use support\Db;
use support\Log;

/**
 * 游戏群组服务
 * @extends BaseService
 */
class TgGameGroupService extends BaseService
{
    #[Inject]
    public TgGameGroupRepository $repository;

    #[Inject]
    protected TgGameGroupConfigRepository $configRepository;

    /**
     * 根据配置ID获取游戏群组
     */
    public function getByConfigId(int $configId)
    {
        return $this->repository->getByConfigId($configId);
    }

    /**
     * 根据Telegram群组ID获取游戏群组
     */
    public function getByTgChatId(int $tgChatId)
    {
        return $this->repository->getByTgChatId($tgChatId);
    }

    /**
     * 初始化游戏群组
     */
    public function initializeGroup(int $configId, int $tgChatId): bool
    {
        try {
            Db::beginTransaction();

            // 检查是否已存在
            $existing = $this->repository->getByConfigId($configId);
            if ($existing) {
                throw new \Exception('游戏群组已存在');
            }

            // 获取配置信息
            $config = $this->configRepository->findById($configId);
            if (!$config) {
                throw new \Exception('配置不存在');
            }

            // 创建游戏群组
            $this->repository->create([
                'config_id' => $configId,
                'tg_chat_id' => $tgChatId,
                'prize_pool_amount' => 0,
                'current_snake_nodes' => '',
                'last_snake_nodes' => '',
                'last_prize_nodes' => '',
                'last_prize_amount' => 0,
                'last_prize_address' => '',
                'last_prize_serial_no' => '',
                'last_prize_at' => null,
                'version' => 1,
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('初始化游戏群组失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 更新奖池金额
     */
    public function updatePrizePool(int $id, float $amount): bool
    {
        return $this->repository->updatePrizePool($id, $amount);
    }

    /**
     * 增加奖池金额
     */
    public function increasePrizePool(int $id, float $amount): bool
    {
        $group = $this->repository->findById($id);
        if (!$group) {
            return false;
        }

        $newAmount = bcadd($group->prize_pool_amount, $amount, 2);
        return $this->repository->updatePrizePool($id, $newAmount);
    }

    /**
     * 减少奖池金额
     */
    public function decreasePrizePool(int $id, float $amount): bool
    {
        $group = $this->repository->findById($id);
        if (!$group) {
            return false;
        }

        $newAmount = bcsub($group->prize_pool_amount, $amount, 2);
        if ($newAmount < 0) {
            $newAmount = 0;
        }

        return $this->repository->updatePrizePool($id, $newAmount);
    }

    /**
     * 更新蛇身节点（添加新节点）
     */
    public function addSnakeNode(int $id, int $nodeId): bool
    {
        $group = $this->repository->findById($id);
        if (!$group) {
            return false;
        }

        // 获取当前蛇身节点
        $currentNodes = $group->current_snake_nodes ? explode(',', $group->current_snake_nodes) : [];
        $currentNodes[] = $nodeId;

        $newNodesStr = implode(',', $currentNodes);

        return $this->repository->updateSnakeNodes($id, $newNodesStr);
    }

    /**
     * 更新蛇身节点（批量设置）
     */
    public function updateSnakeNodes(int $id, array $nodeIds, array $lastNodeIds = null): bool
    {
        $currentNodesStr = implode(',', $nodeIds);
        $lastNodesStr = $lastNodeIds ? implode(',', $lastNodeIds) : null;

        return $this->repository->updateSnakeNodes($id, $currentNodesStr, $lastNodesStr);
    }

    /**
     * 清空当前蛇身（中奖后）
     */
    public function clearCurrentSnake(int $id): bool
    {
        $group = $this->repository->findById($id);
        if (!$group) {
            return false;
        }

        // 将当前蛇身移到历史蛇身
        return $this->repository->updateSnakeNodes($id, '', $group->current_snake_nodes);
    }

    /**
     * 更新最后中奖信息
     */
    public function updateLastPrize(int $id, array $prizeData): bool
    {
        return $this->repository->updateLastPrize($id, $prizeData);
    }

    /**
     * 使用乐观锁更新群组数据
     */
    public function updateWithVersion(int $id, array $data): bool
    {
        $group = $this->repository->findById($id);
        if (!$group) {
            return false;
        }

        return $this->repository->updateWithVersion($id, $data, $group->version);
    }

    /**
     * 获取群组当前蛇身节点ID数组
     */
    public function getCurrentSnakeNodeIds(int $id): array
    {
        $group = $this->repository->findById($id);
        if (!$group || !$group->current_snake_nodes) {
            return [];
        }

        return array_map('intval', explode(',', $group->current_snake_nodes));
    }

    /**
     * 获取群组概览信息
     */
    public function getGroupOverview(int $id): array
    {
        $group = $this->repository->findById($id);
        if (!$group) {
            return [];
        }

        $currentNodes = $group->current_snake_nodes ? explode(',', $group->current_snake_nodes) : [];

        return [
            'id' => $group->id,
            'config_id' => $group->config_id,
            'tg_chat_id' => $group->tg_chat_id,
            'prize_pool_amount' => $group->prize_pool_amount,
            'current_snake_length' => count($currentNodes),
            'last_prize_amount' => $group->last_prize_amount,
            'last_prize_at' => $group->last_prize_at,
            'version' => $group->version,
        ];
    }

    /**
     * 获取群组当前蛇身
     */
    public function getCurrentSnake(int $id): array
    {
        $group = $this->repository->findById($id);
        if (!$group) {
            return [];
        }

        $currentNodeIds = $group->current_snake_nodes ? explode(',', $group->current_snake_nodes) : [];
        $lastNodeIds = $group->last_snake_nodes ? explode(',', $group->last_snake_nodes) : [];

        return [
            'group_id' => $group->id,
            'current_nodes' => $currentNodeIds,
            'current_length' => count($currentNodeIds),
            'last_nodes' => $lastNodeIds,
            'last_length' => count($lastNodeIds),
            'prize_pool_amount' => $group->prize_pool_amount,
        ];
    }

    /**
     * 重置群组奖池
     */
    public function resetPrizePool(int $id): array
    {
        try {
            $group = $this->repository->findById($id);
            if (!$group) {
                return [
                    'success' => false,
                    'message' => '群组不存在',
                ];
            }

            $result = $this->repository->updatePrizePool($id, 0);

            return [
                'success' => $result,
                'message' => $result ? '奖池已重置' : '奖池重置失败',
            ];
        } catch (\Exception $e) {
            Log::error('重置奖池失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取群组统计信息
     */
    public function getGroupStatistics(int $groupId = null): array
    {
        $query = Db::table('tg_game_group');

        if ($groupId) {
            $query->where('id', $groupId);
        }

        $stats = [
            'total_groups' => (clone $query)->count(),
            'total_prize_pool' => (clone $query)->sum('prize_pool_amount'),
            'avg_prize_pool' => (clone $query)->avg('prize_pool_amount'),
        ];

        return $stats;
    }
}
