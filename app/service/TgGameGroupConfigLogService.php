<?php

namespace app\service;

use app\repository\TgGameGroupConfigLogRepository;
use DI\Attribute\Inject;
use Illuminate\Support\Collection;

/**
 * 游戏群组配置变更日志服务
 * @extends BaseService
 */
class TgGameGroupConfigLogService extends BaseService
{
    #[Inject]
    public TgGameGroupConfigLogRepository $repository;

    /**
     * 记录配置变更日志
     */
    public function logConfigChange(array $data)
    {
        return $this->repository->logConfigChange($data);
    }

    /**
     * 获取配置的变更历史
     */
    public function getConfigHistory(int $configId, int $limit = 20): Collection
    {
        return $this->repository->getConfigHistory($configId, $limit);
    }

    /**
     * 根据Telegram群组ID获取变更历史
     */
    public function getHistoryByTgChatId(int $tgChatId, int $limit = 20): Collection
    {
        return $this->repository->getHistoryByTgChatId($tgChatId, $limit);
    }

    /**
     * 根据变更来源查询
     */
    public function getByChangeSource(int $changeSource, int $limit = 50): Collection
    {
        return $this->repository->getByChangeSource($changeSource, $limit);
    }

    /**
     * 根据操作人查询
     */
    public function getByOperator(string $operator, int $limit = 50): Collection
    {
        return $this->repository->getByOperator($operator, $limit);
    }

    /**
     * 获取TG消息相关的变更记录
     */
    public function getByTgMessageId(int $tgChatId, int $tgMessageId)
    {
        return $this->repository->getByTgMessageId($tgChatId, $tgMessageId);
    }

    /**
     * 统计配置变更次数
     */
    public function countConfigChanges(int $configId, string $dateStart = null, string $dateEnd = null): int
    {
        return $this->repository->countConfigChanges($configId, $dateStart, $dateEnd);
    }

    /**
     * 统计变更来源分布
     */
    public function getChangeSourceStatistics(int $configId = null): array
    {
        return $this->repository->getChangeSourceStatistics($configId);
    }

    /**
     * 获取最近的变更记录
     */
    public function getRecentLogs(int $limit = 10): Collection
    {
        return $this->repository->getRecentLogs($limit);
    }

    /**
     * 获取指定配置的最后一次变更
     */
    public function getLastChange(int $configId)
    {
        return $this->repository->getLastChange($configId);
    }

    /**
     * 清理旧日志（超过指定天数）
     */
    public function cleanOldLogs(int $daysAgo = 180): int
    {
        return $this->repository->cleanOldLogs($daysAgo);
    }

    /**
     * 获取指定时间段内的变更记录
     */
    public function getChangesByDateRange(int $configId, string $startDate, string $endDate): Collection
    {
        return $this->repository->getChangesByDateRange($configId, $startDate, $endDate);
    }

    /**
     * 分页获取配置日志（用于Controller）
     */
    public function getConfigLogPage(array $params, int $page = 1, int $pageSize = 10): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    /**
     * 根据操作类型查询日志
     */
    public function getLogsByAction(string $action, int $limit = 100): Collection
    {
        // 这里可以根据需要添加筛选逻辑
        $params = ['action' => $action];
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 获取钱包变更历史
     */
    public function getWalletChangeHistory(int $configId): Collection
    {
        // 筛选钱包相关变更记录
        return $this->repository->getConfigHistory($configId, 100)
            ->filter(function ($log) {
                if (empty($log->change_params)) {
                    return false;
                }
                $params = json_decode($log->change_params, true);
                return isset($params['wallet_address']) ||
                       isset($params['pending_wallet_address']) ||
                       isset($params['wallet_change_status']);
            });
    }

    /**
     * 获取配置日志详情
     */
    public function getConfigLogById(int $id)
    {
        return $this->repository->findById($id);
    }

    /**
     * 获取配置日志导出数据
     */
    public function getConfigLogExportData(array $params, int $limit = 10000): Collection
    {
        return $this->repository->list($params)->take($limit);
    }
}
