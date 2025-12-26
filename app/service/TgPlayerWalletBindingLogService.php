<?php

namespace app\service;

use app\repository\TgPlayerWalletBindingLogRepository;
use DI\Attribute\Inject;
use Illuminate\Support\Collection;

/**
 * 玩家钱包绑定日志服务
 * @extends BaseService
 */
class TgPlayerWalletBindingLogService extends BaseService
{
    #[Inject]
    public TgPlayerWalletBindingLogRepository $repository;

    /**
     * 记录绑定变更日志
     */
    public function logChange(array $data)
    {
        return $this->repository->logChange($data);
    }

    /**
     * 获取用户的绑定历史
     */
    public function getUserBindingHistory(int $groupId, int $tgUserId, int $limit = 20): Collection
    {
        return $this->repository->getUserBindingHistory($groupId, $tgUserId, $limit);
    }

    /**
     * 获取钱包地址的绑定历史
     */
    public function getWalletBindingHistory(string $walletAddress, int $limit = 20): Collection
    {
        return $this->repository->getWalletBindingHistory($walletAddress, $limit);
    }

    /**
     * 获取群组的绑定变更记录
     */
    public function getGroupBindingLogs(int $groupId, int $limit = 50): Collection
    {
        return $this->repository->getGroupBindingLogs($groupId, $limit);
    }

    /**
     * 根据变更类型查询
     */
    public function getByChangeType(int $groupId, int $changeType, int $limit = 50): Collection
    {
        return $this->repository->getByChangeType($groupId, $changeType, $limit);
    }

    /**
     * 统计用户绑定变更次数
     */
    public function countUserChanges(int $groupId, int $tgUserId): int
    {
        return $this->repository->countUserChanges($groupId, $tgUserId);
    }

    /**
     * 统计群组绑定变更数据
     */
    public function getGroupStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        return $this->repository->getGroupStatistics($groupId, $dateStart, $dateEnd);
    }

    /**
     * 获取最近的绑定变更记录
     */
    public function getRecentLogs(int $groupId, int $limit = 10): Collection
    {
        return $this->repository->getRecentLogs($groupId, $limit);
    }

    /**
     * 清理旧日志（超过指定天数）
     */
    public function cleanOldLogs(int $daysAgo = 180): int
    {
        return $this->repository->cleanOldLogs($daysAgo);
    }

    /**
     * 分页获取绑定日志（用于Controller）
     */
    public function getBindingLogPage(array $params, int $page = 1, int $pageSize = 10): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    /**
     * 获取用户绑定历史（别名方法，用于Controller）
     */
    public function getBindingHistory(int $groupId, int $tgUserId, int $limit = 50): Collection
    {
        return $this->getUserBindingHistory($groupId, $tgUserId, $limit);
    }

    /**
     * 根据钱包地址查询日志
     */
    public function getLogsByWalletAddress(string $walletAddress): Collection
    {
        return $this->getWalletBindingHistory($walletAddress, 100);
    }

    /**
     * 根据操作类型查询日志
     */
    public function getLogsByAction(string $action, int $limit = 100): Collection
    {
        // 这里的action实际对应change_type
        // 1=首次绑定, 2=更新绑定
        $changeTypeMap = [
            'bind' => 1,
            'first_bind' => 1,
            'update' => 2,
            'update_bind' => 2,
        ];

        $changeType = $changeTypeMap[$action] ?? null;
        if ($changeType === null) {
            return collect([]);
        }

        // 获取所有群组的该类型变更
        $params = ['change_type' => $changeType];
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 获取绑定日志详情
     */
    public function getBindingLogById(int $id)
    {
        return $this->repository->findById($id);
    }

    /**
     * 获取绑定日志导出数据
     */
    public function getBindingLogExportData(array $params, int $limit = 10000): Collection
    {
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 记录首次绑定
     */
    public function logFirstBinding(int $groupId, int $tgUserId, array $userData, string $walletAddress)
    {
        return $this->logChange([
            'group_id' => $groupId,
            'tg_user_id' => $tgUserId,
            'tg_username' => $userData['username'] ?? null,
            'tg_first_name' => $userData['first_name'] ?? null,
            'tg_last_name' => $userData['last_name'] ?? null,
            'old_wallet_address' => '',
            'new_wallet_address' => $walletAddress,
            'change_type' => 1, // 首次绑定
        ]);
    }

    /**
     * 记录更新绑定
     */
    public function logUpdateBinding(int $groupId, int $tgUserId, array $userData, string $oldWallet, string $newWallet)
    {
        return $this->logChange([
            'group_id' => $groupId,
            'tg_user_id' => $tgUserId,
            'tg_username' => $userData['username'] ?? null,
            'tg_first_name' => $userData['first_name'] ?? null,
            'tg_last_name' => $userData['last_name'] ?? null,
            'old_wallet_address' => $oldWallet,
            'new_wallet_address' => $newWallet,
            'change_type' => 2, // 更新绑定
        ]);
    }
}
