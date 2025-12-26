<?php

namespace app\service;

use app\constants\TgPlayerWalletBindingLog as BindingLogConst;
use app\repository\TgPlayerWalletBindingRepository;
use app\repository\TgPlayerWalletBindingLogRepository;
use DI\Attribute\Inject;
use support\Db;
use support\Log;

/**
 * 玩家钱包绑定服务
 * @extends BaseService
 */
class TgPlayerWalletBindingService extends BaseService
{
    #[Inject]
    public TgPlayerWalletBindingRepository $repository;

    #[Inject]
    protected TgPlayerWalletBindingLogRepository $bindingLogRepository;

    /**
     * 根据Telegram用户ID获取绑定
     */
    public function getByTgUserId(int $groupId, int $tgUserId)
    {
        return $this->repository->getByTgUserId($groupId, $tgUserId);
    }

    /**
     * 根据钱包地址查询绑定
     */
    public function getByWalletAddress(string $walletAddress, int $groupId = null)
    {
        return $this->repository->getByWalletAddress($walletAddress, $groupId);
    }

    /**
     * 根据用户名查询绑定
     */
    public function getByUsername(int $groupId, string $username)
    {
        return $this->repository->getByUsername($groupId, $username);
    }

    /**
     * 绑定钱包
     */
    public function bindWallet(array $data): array
    {
        try {
            Db::beginTransaction();

            // 检查钱包地址在群组中是否已被其他人绑定
            $isBound = $this->repository->isWalletBoundInGroup(
                $data['group_id'],
                $data['wallet_address'],
                $data['tg_user_id']
            );

            if ($isBound) {
                throw new \Exception('该钱包地址已被其他玩家绑定');
            }

            // 检查用户是否已有绑定
            $existing = $this->repository->getByTgUserId($data['group_id'], $data['tg_user_id']);

            $oldWalletAddress = $existing ? $existing->wallet_address : '';
            $changeType = $existing ? BindingLogConst::CHANGE_TYPE_UPDATE_BIND : BindingLogConst::CHANGE_TYPE_FIRST_BIND;

            // 创建或更新绑定
            $binding = $this->repository->createOrUpdate(array_merge($data, [
                'bind_at' => $existing ? $existing->bind_at : now(),
            ]));

            // 记录变更日志
            $this->bindingLogRepository->logChange([
                'group_id' => $data['group_id'],
                'tg_user_id' => $data['tg_user_id'],
                'tg_username' => $data['tg_username'] ?? '',
                'tg_first_name' => $data['tg_first_name'] ?? '',
                'tg_last_name' => $data['tg_last_name'] ?? '',
                'old_wallet_address' => $oldWalletAddress,
                'new_wallet_address' => $data['wallet_address'],
                'change_type' => $changeType,
            ]);

            Db::commit();

            return [
                'success' => true,
                'message' => $changeType == BindingLogConst::CHANGE_TYPE_FIRST_BIND ? '绑定成功' : '更新绑定成功',
                'binding' => $binding,
                'is_first_bind' => $changeType == BindingLogConst::CHANGE_TYPE_FIRST_BIND,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('绑定钱包失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 解绑钱包
     */
    public function unbindWallet(int $groupId, int $tgUserId): array
    {
        try {
            Db::beginTransaction();

            $binding = $this->repository->getByTgUserId($groupId, $tgUserId);
            if (!$binding) {
                throw new \Exception('未找到绑定记录');
            }

            // 删除绑定
            $success = $this->repository->deleteBinding($groupId, $tgUserId);
            if (!$success) {
                throw new \Exception('解绑失败');
            }

            // 记录日志
            $this->bindingLogRepository->logChange([
                'group_id' => $groupId,
                'tg_user_id' => $tgUserId,
                'tg_username' => $binding->tg_username ?? '',
                'tg_first_name' => $binding->tg_first_name ?? '',
                'tg_last_name' => $binding->tg_last_name ?? '',
                'old_wallet_address' => $binding->wallet_address,
                'new_wallet_address' => '',
                'change_type' => BindingLogConst::CHANGE_TYPE_UNBIND,
            ]);

            Db::commit();

            return [
                'success' => true,
                'message' => '解绑成功',
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('解绑钱包失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 通过钱包地址反查Telegram用户信息
     */
    public function getUserByWalletAddress(int $groupId, string $walletAddress)
    {
        return $this->repository->getUserByWalletAddress($groupId, $walletAddress);
    }

    /**
     * 获取用户的绑定历史
     */
    public function getUserBindingHistory(int $groupId, int $tgUserId, int $limit = 20)
    {
        return $this->bindingLogRepository->getUserBindingHistory($groupId, $tgUserId, $limit);
    }

    /**
     * 获取群组的所有绑定
     */
    public function getGroupBindings(int $groupId)
    {
        return $this->repository->getGroupBindings($groupId);
    }

    /**
     * 统计群组绑定数量
     */
    public function countGroupBindings(int $groupId): int
    {
        return $this->repository->countGroupBindings($groupId);
    }

    /**
     * 根据TG用户ID获取用户绑定
     */
    public function getUserByTgUserId(int $groupId, int $tgUserId)
    {
        return $this->repository->getByTgUserId($groupId, $tgUserId);
    }

    /**
     * 根据群组ID获取所有绑定
     */
    public function getByGroupId(int $groupId)
    {
        return $this->repository->getGroupBindings($groupId);
    }

    /**
     * 获取玩家的参与节点
     */
    public function getPlayerNodes(int $groupId, int $tgUserId)
    {
        $binding = $this->repository->getByTgUserId($groupId, $tgUserId);
        if (!$binding) {
            return [];
        }

        // 查询该玩家的所有节点
        return Db::table('tg_snake_node')
            ->where('group_id', $groupId)
            ->where('wallet_address', $binding->wallet_address)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 获取玩家统计信息
     */
    public function getPlayerStatistics(int $groupId, int $tgUserId): array
    {
        $binding = $this->repository->getByTgUserId($groupId, $tgUserId);
        if (!$binding) {
            return [
                'total_nodes' => 0,
                'active_nodes' => 0,
                'archived_nodes' => 0,
                'total_amount' => 0,
            ];
        }

        $walletAddress = $binding->wallet_address;

        $totalNodes = Db::table('tg_snake_node')
            ->where('group_id', $groupId)
            ->where('wallet_address', $walletAddress)
            ->count();

        $activeNodes = Db::table('tg_snake_node')
            ->where('group_id', $groupId)
            ->where('wallet_address', $walletAddress)
            ->where('status', 1) // 假设1是活跃状态
            ->count();

        $archivedNodes = Db::table('tg_snake_node')
            ->where('group_id', $groupId)
            ->where('wallet_address', $walletAddress)
            ->where('status', 2) // 假设2是归档状态
            ->count();

        $totalAmount = Db::table('tg_snake_node')
            ->where('group_id', $groupId)
            ->where('wallet_address', $walletAddress)
            ->sum('amount');

        return [
            'total_nodes' => $totalNodes,
            'active_nodes' => $activeNodes,
            'archived_nodes' => $archivedNodes,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * 获取绑定变更日志
     */
    public function getBindingLogs(int $groupId, int $tgUserId, int $limit = 20)
    {
        return $this->bindingLogRepository->getUserBindingHistory($groupId, $tgUserId, $limit);
    }

    /**
     * 批量绑定钱包
     */
    public function batchBindWallets(int $groupId, array $bindings): array
    {
        try {
            Db::beginTransaction();

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($bindings as $index => $bindingData) {
                try {
                    $result = $this->bindWallet([
                        'group_id' => $groupId,
                        'tg_user_id' => $bindingData['tg_user_id'],
                        'tg_username' => $bindingData['tg_username'] ?? null,
                        'tg_first_name' => $bindingData['tg_first_name'] ?? null,
                        'tg_last_name' => $bindingData['tg_last_name'] ?? null,
                        'wallet_address' => $bindingData['wallet_address'],
                    ]);

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failedCount++;
                        $errors[] = [
                            'index' => $index,
                            'tg_user_id' => $bindingData['tg_user_id'],
                            'error' => $result['message'],
                        ];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'index' => $index,
                        'tg_user_id' => $bindingData['tg_user_id'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            Db::commit();

            return [
                'success' => true,
                'total' => count($bindings),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('批量绑定钱包失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
