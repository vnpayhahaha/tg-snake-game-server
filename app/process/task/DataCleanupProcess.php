<?php

namespace app\process\task;

use app\constants\TgPrizeDispatchQueue as QueueConst;
use Carbon\Carbon;
use support\Db;
use support\Log;
use Workerman\Crontab\Crontab;

/**
 * 数据清理进程
 * 定时清理过期数据，保持数据库性能
 */
class DataCleanupProcess
{
    public function onWorkerStart(): void
    {
        Log::info("DataCleanupProcess: 进程启动");

        // 每天凌晨3点执行数据清理
        new Crontab('0 0 3 * * *', function() {
            try {
                $this->cleanupExpiredData();
            } catch (\Throwable $e) {
                Log::error("DataCleanupProcess执行失败: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        Log::info("DataCleanupProcess: Crontab已设置 (每天凌晨3点执行)");
    }

    /**
     * 清理过期数据
     */
    protected function cleanupExpiredData(): void
    {
        Log::info("DataCleanupProcess: 开始清理过期数据");

        $startTime = microtime(true);

        // 清理已完成的派发队列记录（保留30天）
        $queueRecordsDeleted = $this->cleanupCompletedQueueRecords(30);

        // 清理已处理的交易日志（保留180天）
        $txLogsDeleted = $this->cleanupProcessedTransactionLogs(180);

        // 清理取消的派发队列记录（保留7天）
        $cancelledQueueDeleted = $this->cleanupCancelledQueueRecords(7);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        Log::info("DataCleanupProcess: 清理完成", [
            'duration_seconds' => $duration,
            'queue_records_deleted' => $queueRecordsDeleted,
            'tx_logs_deleted' => $txLogsDeleted,
            'cancelled_queue_deleted' => $cancelledQueueDeleted,
        ]);
    }

    /**
     * 清理已完成的派发队列记录
     * @param int $daysToKeep 保留天数
     * @return int 删除的记录数
     */
    protected function cleanupCompletedQueueRecords(int $daysToKeep): int
    {
        try {
            $cutoffDate = Carbon::now()->subDays($daysToKeep);

            $deleted = Db::table('tg_prize_dispatch_queue')
                ->where('status', QueueConst::STATUS_COMPLETED)
                ->where('process_end_time', '<', $cutoffDate)
                ->delete();

            Log::info("清理已完成派发队列记录", [
                'days_to_keep' => $daysToKeep,
                'cutoff_date' => $cutoffDate->toDateString(),
                'deleted' => $deleted,
            ]);

            return $deleted;

        } catch (\Throwable $e) {
            Log::error("清理已完成派发队列记录失败: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 清理已处理的交易日志
     * @param int $daysToKeep 保留天数
     * @return int 删除的记录数
     */
    protected function cleanupProcessedTransactionLogs(int $daysToKeep): int
    {
        try {
            $cutoffDate = Carbon::now()->subDays($daysToKeep);

            $deleted = Db::table('tg_tron_transaction_log')
                ->where('processed', 1)
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            Log::info("清理已处理交易日志", [
                'days_to_keep' => $daysToKeep,
                'cutoff_date' => $cutoffDate->toDateString(),
                'deleted' => $deleted,
            ]);

            return $deleted;

        } catch (\Throwable $e) {
            Log::error("清理已处理交易日志失败: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 清理取消的派发队列记录
     * @param int $daysToKeep 保留天数
     * @return int 删除的记录数
     */
    protected function cleanupCancelledQueueRecords(int $daysToKeep): int
    {
        try {
            $cutoffDate = Carbon::now()->subDays($daysToKeep);

            $deleted = Db::table('tg_prize_dispatch_queue')
                ->where('status', QueueConst::STATUS_CANCELLED)
                ->where('updated_at', '<', $cutoffDate)
                ->delete();

            Log::info("清理取消的派发队列记录", [
                'days_to_keep' => $daysToKeep,
                'cutoff_date' => $cutoffDate->toDateString(),
                'deleted' => $deleted,
            ]);

            return $deleted;

        } catch (\Throwable $e) {
            Log::error("清理取消的派发队列记录失败: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 优化数据库表（可选）
     */
    protected function optimizeTables(): void
    {
        try {
            $tables = [
                'tg_snake_node',
                'tg_prize_dispatch_queue',
                'tg_tron_transaction_log',
            ];

            foreach ($tables as $table) {
                Db::statement("OPTIMIZE TABLE {$table}");
                Log::debug("优化数据表: {$table}");
            }

            Log::info("数据表优化完成");

        } catch (\Throwable $e) {
            Log::error("数据表优化失败: " . $e->getMessage());
        }
    }
}
