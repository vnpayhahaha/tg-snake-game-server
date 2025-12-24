<?php

namespace app\service;

use app\constants\CollectionOrder;
use app\constants\DisbursementOrder;
use app\repository\BankAccountRepository;
use app\repository\ChannelAccountDailyStatsRepository;
use app\repository\ChannelAccountRepository;
use app\repository\CollectionOrderRepository;
use app\repository\DisbursementOrderRepository;
use Carbon\Carbon;
use DI\Attribute\Inject;
use support\Log;
use support\Db;
use Throwable;

final class ChannelAccountDailyStatsService extends IService
{
    #[Inject]
    public ChannelAccountDailyStatsRepository $repository;

    #[Inject]
    public ChannelAccountRepository $channelAccountRepository;

    #[Inject]
    public BankAccountRepository $bankAccountRepository;

    #[Inject]
    public CollectionOrderRepository $collectionOrderRepository;

    #[Inject]
    public DisbursementOrderRepository $disbursementOrderRepository;

    // 常量定义
    private const BATCH_SIZE = 50;
    private const BATCH_SLEEP_MICROSECONDS = 100000;
    private const ACCOUNT_STATUS_ENABLED = 1;
    private const LIMIT_STATUS_NORMAL = 0;
    private const LIMIT_STATUS_PARTIAL = 1;
    private const LIMIT_STATUS_FULL = 2;

    // 缓存账户配置，避免重复查询
    private array $accountCache = [];

    // 每分钟定时统计
    public function minutelyStatsCron(): void
    {
        $currentTime = Carbon::now();
        $todayDate = $currentTime->format('Y-m-d');

        try {
            Log::info("开始每分钟统计渠道账户数据", [
                'stat_date'    => $todayDate,
                'current_time' => $currentTime->format('Y-m-d H:i:s')
            ]);

            // 统计所有账户的每日数据
            $this->processAllAccountStats($todayDate);

            Log::info("每分钟渠道账户统计任务完成", [
                'stat_date'      => $todayDate,
                'execution_time' => $currentTime->format('Y-m-d H:i:s')
            ]);
        } catch (Throwable $e) {
            Log::error("每分钟渠道账户统计任务失败", [
                'stat_date'     => $todayDate,
                'current_time'  => $currentTime->format('Y-m-d H:i:s'),
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile() . ':' . $e->getLine(),
                'trace'         => $e->getTraceAsString()
            ]);

            throw $e;
        } finally {
            // 清理缓存
            $this->accountCache = [];
        }
    }

    // 每日定时1点统计（保留用于历史数据统计）
    public function dailyStatsCron(): void
    {
        $yesterdayDate = Carbon::yesterday()->format('Y-m-d');

        try {
            Log::info("开始统计渠道账户每日数据", ['stat_date' => $yesterdayDate]);

            // 统计所有账户的每日数据
            $this->processAllAccountStats($yesterdayDate);

            Log::info("渠道账户每日统计任务完成", ['stat_date' => $yesterdayDate]);
        } catch (Throwable $e) {
            Log::error("渠道账户每日统计任务失败", [
                'stat_date'     => $yesterdayDate,
                'error_message' => $e->getMessage(),
                'error_file'    => $e->getFile() . ':' . $e->getLine(),
                'trace'         => $e->getTraceAsString()
            ]);

            throw $e;
        } finally {
            // 清理缓存
            $this->accountCache = [];
        }
    }

    /**
     * 统计所有账户的每日数据 - 批量优化版本
     */
    private function processAllAccountStats(string $statDate): void
    {
        // 获取所有活跃的账户ID（有交易的账户）
        $activeAccounts = $this->getActiveAccounts($statDate);

        // 获取所有应该统计的账户（包括无交易的启用账户）
        $allAccounts = $this->getAllEnabledAccounts($statDate);

        // 合并账户列表，确保所有启用账户都有统计记录
        $accountsToProcess = $this->mergeAccountLists($activeAccounts, $allAccounts);

        if (empty($accountsToProcess)) {
            Log::info("当日无需统计的账户", ['stat_date' => $statDate]);
            return;
        }

        // 批量预载入账户信息到缓存
        $this->preloadAccountCache($accountsToProcess);

        // 使用批量处理减少数据库压力
        $batches = array_chunk($accountsToProcess, self::BATCH_SIZE);

        foreach ($batches as $batchIndex => $batch) {
            Log::debug("处理账户批次", [
                'stat_date'     => $statDate,
                'batch_index'   => $batchIndex + 1,
                'batch_size'    => count($batch),
                'total_batches' => count($batches)
            ]);

            foreach ($batch as $account) {
                try {
                    $this->processAccountDailyStats($account, $statDate);
                } catch (Throwable $e) {
                    Log::error("处理账户统计失败", [
                        'stat_date' => $statDate,
                        'account'   => $account,
                        'error'     => $e->getMessage()
                    ]);
                    // 继续处理其他账户，不中断整个批次
                }
            }

            // 批次间短暂休息，避免数据库压力过大
            if ($batchIndex < count($batches) - 1) {
                usleep(self::BATCH_SLEEP_MICROSECONDS); // 休息100ms
            }
        }

        Log::info("账户每日统计数据处理完成", [
            'stat_date'          => $statDate,
            'processed_accounts' => count($accountsToProcess),
            'processed_batches'  => count($batches)
        ]);
    }

    /**
     * 批量预载入账户信息到缓存
     */
    private function preloadAccountCache(array $activeAccounts): void
    {
        // 分组收集账户ID
        $channelAccountIds = [];
        $bankAccountIds = [];

        foreach ($activeAccounts as $account) {
            if ($account['type'] === 'channel') {
                $channelAccountIds[] = $account['account_id'];
            } else {
                $bankAccountIds[] = $account['account_id'];
            }
        }

        // 批量查询渠道账户 - 使用 IN 查询优化
        if (!empty($channelAccountIds)) {
            $uniqueChannelIds = array_unique($channelAccountIds);
            $channelAccounts = $this->channelAccountRepository->getQuery()
                ->whereIn('id', $uniqueChannelIds)
                ->select(['id', 'channel_id', 'daily_max_receipt', 'daily_max_receipt_count',
                         'daily_max_payment', 'daily_max_payment_count', 'limit_quota',
                         'today_receipt_amount', 'today_receipt_count', 'today_payment_amount',
                         'today_payment_count', 'used_quota'])
                ->get()
                ->keyBy('id');

            foreach ($channelAccounts as $account) {
                $this->accountCache['channel_' . $account->id] = $account;
            }
        }

        // 批量查询银行账户 - 使用 IN 查询优化
        if (!empty($bankAccountIds)) {
            $uniqueBankIds = array_unique($bankAccountIds);
            $bankAccounts = $this->bankAccountRepository->getQuery()
                ->whereIn('id', $uniqueBankIds)
                ->select(['id', 'channel_id', 'daily_max_receipt', 'daily_max_receipt_count',
                         'daily_max_payment', 'daily_max_payment_count', 'limit_quota',
                         'today_receipt_amount', 'today_receipt_count', 'today_payment_amount',
                         'today_payment_count', 'used_quota'])
                ->get()
                ->keyBy('id');

            foreach ($bankAccounts as $account) {
                $this->accountCache['bank_' . $account->id] = $account;
            }
        }

        Log::debug("预载入账户缓存完成", [
            'channel_accounts' => count($channelAccountIds),
            'bank_accounts'    => count($bankAccountIds),
            'cached_items'     => count($this->accountCache)
        ]);
    }

    /**
     * 获取活跃账户（有交易记录的账户）- 优化版本
     */
    private function getActiveAccounts(string $statDate): array
    {
        // 优化的 SQL 查询 - 使用索引友好的查询，在数据库层面去重
        // 注意：确保在 collection_order 和 disbursement_order 表上创建如下复合索引：
        // INDEX idx_created_channel_account (created_at, channel_account_id)
        // INDEX idx_created_bank_account (created_at, bank_account_id)
        $unionSql = <<<SQL
            SELECT DISTINCT
                account_id,
                channel_id,
                type
            FROM (
                (SELECT DISTINCT
                    channel_account_id as account_id,
                    collection_channel_id as channel_id,
                    'channel' as type
                FROM collection_order
                WHERE created_at >= ? AND created_at < ?
                  AND channel_account_id IS NOT NULL
                  AND channel_account_id > 0)
                UNION ALL
                (SELECT DISTINCT
                    channel_account_id as account_id,
                    disbursement_channel_id as channel_id,
                    'channel' as type
                FROM disbursement_order
                WHERE created_at >= ? AND created_at < ?
                  AND channel_account_id IS NOT NULL
                  AND channel_account_id > 0)
                UNION ALL
                (SELECT DISTINCT
                    bank_account_id as account_id,
                    collection_channel_id as channel_id,
                    'bank' as type
                FROM collection_order
                WHERE created_at >= ? AND created_at < ?
                  AND bank_account_id IS NOT NULL
                  AND bank_account_id > 0
                  AND channel_account_id IS NULL)
                UNION ALL
                (SELECT DISTINCT
                    bank_account_id as account_id,
                    disbursement_channel_id as channel_id,
                    'bank' as type
                FROM disbursement_order
                WHERE created_at >= ? AND created_at < ?
                  AND bank_account_id IS NOT NULL
                  AND bank_account_id > 0
                  AND channel_account_id IS NULL)
            ) AS combined_accounts
        SQL;

        // 使用时间范围查询代替 DATE() 函数以提高性能
        $startTime = $statDate . ' 00:00:00';
        $endTime = $statDate . ' 23:59:59';
        $results = Db::select($unionSql, [
            $startTime, $endTime,
            $startTime, $endTime,
            $startTime, $endTime,
            $startTime, $endTime
        ]);

        // 直接转换结果，过滤无效账户ID
        return array_filter(array_map(function ($item) {
            return [
                'type'       => $item->type,
                'account_id' => (int)$item->account_id,
                'channel_id' => (int)$item->channel_id,
            ];
        }, $results), function ($account) {
            // 过滤掉账户ID为0或负数的记录
            return $account['account_id'] > 0;
        });
    }

    /**
     * 获取所有启用的账户（包括无交易的账户）
     */
    private function getAllEnabledAccounts(string $statDate): array
    {
        $accounts = [];

        // 获取所有启用的渠道账户
        $channelAccounts = $this->channelAccountRepository->getQuery()
            ->where('status', self::ACCOUNT_STATUS_ENABLED)
            ->select('id as account_id', 'channel_id')
            ->get();

        foreach ($channelAccounts as $account) {
            $accounts[] = [
                'type' => 'channel',
                'account_id' => $account->account_id,
                'channel_id' => $account->channel_id,
            ];
        }

        // 获取所有启用的银行账户
        $bankAccounts = $this->bankAccountRepository->getQuery()
            ->where('status', self::ACCOUNT_STATUS_ENABLED)
            ->select('id as account_id', 'channel_id')
            ->get();

        foreach ($bankAccounts as $account) {
            $accounts[] = [
                'type' => 'bank',
                'account_id' => $account->account_id,
                'channel_id' => $account->channel_id,
            ];
        }

        return $accounts;
    }

    /**
     * 合并账户列表，去重并确保所有账户都被包含
     */
    private function mergeAccountLists(array $activeAccounts, array $allAccounts): array
    {
        // 使用关联数组进行去重
        $merged = [];

        // 先添加有交易的账户
        foreach ($activeAccounts as $account) {
            $key = $account['type'] . '_' . $account['account_id'];
            $merged[$key] = $account;
        }

        // 再添加所有启用账户，如果已存在则跳过
        foreach ($allAccounts as $account) {
            $key = $account['type'] . '_' . $account['account_id'];
            if (!isset($merged[$key])) {
                $merged[$key] = $account;
            }
        }

        return array_values($merged);
    }

    /**
     * 处理单个账户的每日统计
     */
    private function processAccountDailyStats(array $accountInfo, string $statDate): void
    {
        // 统计收款数据
        $collectionStats = $this->getOrderStats($accountInfo, $statDate, 'collection');

        // 统计付款数据
        $disbursementStats = $this->getOrderStats($accountInfo, $statDate, 'disbursement');

        // 合并统计数据并保存
        $this->upsertDailyStats($accountInfo, $statDate, $collectionStats, $disbursementStats);
    }

    /**
     * 获取订单统计数据 - 优化版本
     */
    private function getOrderStats(array $accountInfo, string $statDate, string $type): array
    {
        $isCollection = $type === 'collection';
        $repository = $isCollection ? $this->collectionOrderRepository : $this->disbursementOrderRepository;
        $successStatus = $isCollection ? CollectionOrder::STATUS_SUCCESS : DisbursementOrder::STATUS_SUCCESS;
        $amountField = $isCollection ? 'paid_amount' : 'amount';
        $accountField = $accountInfo['type'] === 'channel' ? 'channel_account_id' : 'bank_account_id';

        try {
            // 使用时间范围查询替代 whereDate 以提高性能
            $startTime = $statDate . ' 00:00:00';
            $endTime = $statDate . ' 23:59:59';

            $stats = $repository->getQuery()
                ->selectRaw(
                    "COUNT(*) as transaction_count,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as failure_count,
                    SUM(CASE WHEN status = ? THEN {$amountField} ELSE 0 END) as amount_total,
                    AVG(CASE WHEN status = ? AND pay_time IS NOT NULL
                        THEN TIMESTAMPDIFF(SECOND, created_at, pay_time)
                        ELSE NULL END) as avg_process_time",
                    [$successStatus, $successStatus, $successStatus, $successStatus]
                )
                ->whereBetween('created_at', [$startTime, $endTime])
                ->where($accountField, $accountInfo['account_id'])
                ->first();

            return [
                'transaction_count' => (int)($stats->transaction_count ?? 0),
                'success_count'     => (int)($stats->success_count ?? 0),
                'failure_count'     => (int)($stats->failure_count ?? 0),
                'amount_total'      => (float)($stats->amount_total ?? 0),
                'avg_process_time'  => (float)($stats->avg_process_time ?? 0),
            ];
        } catch (Throwable $e) {
            Log::error("获取订单统计数据失败", [
                'account_info' => $accountInfo,
                'stat_date'    => $statDate,
                'type'         => $type,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString()
            ]);

            // 返回默认值以避免中断流程
            return [
                'transaction_count' => 0,
                'success_count'     => 0,
                'failure_count'     => 0,
                'amount_total'      => 0,
                'avg_process_time'  => 0,
            ];
        }
    }

    /**
     * 更新或插入每日统计数据 - 优化版本
     */
    private function upsertDailyStats(array $accountInfo, string $statDate, array $collectionStats, array $disbursementStats): void
    {
        $accountType = $accountInfo['type'];
        $accountId = $accountInfo['account_id'];
        $channelId = $accountInfo['channel_id'];

        // 数据验证：账户ID必须大于0
        if (!$this->validateAccountId($accountId, $accountInfo, $statDate)) {
            return;
        }

        try {
            // 构建账户ID映射
            [$channelAccountId, $bankAccountId] = $this->buildAccountIdMapping($accountType, $accountId);

            // 查询现有记录
            $existingRecord = $this->repository->findByAccountAndDate($channelAccountId, $bankAccountId, $statDate);

            // 计算成功率
            [$collectionSuccessRate, $disbursementSuccessRate] = $this->calculateSuccessRates($collectionStats, $disbursementStats);

            // 构建统计数据
            $data = $this->buildStatsData([
                'channel_account_id'         => $channelAccountId,
                'bank_account_id'            => $bankAccountId,
                'channel_id'                 => $channelId,
                'stat_date'                  => $statDate,
                'collection_stats'           => $collectionStats,
                'disbursement_stats'         => $disbursementStats,
                'collection_success_rate'    => $collectionSuccessRate,
                'disbursement_success_rate'  => $disbursementSuccessRate,
                'account_type'               => $accountType,
                'account_id'                 => $accountId,
            ]);

            // 保存或更新记录
            $record = $this->saveStatsRecord($channelAccountId, $bankAccountId, $statDate, $data);

            Log::debug($existingRecord ? "更新每日统计记录" : "创建每日统计记录", [
                'record_id'    => $record->id,
                'account_type' => $accountType,
                'account_id'   => $accountId,
                'stat_date'    => $statDate
            ]);

        } catch (Throwable $e) {
            Log::error("保存每日统计数据失败", [
                'account_info'        => $accountInfo,
                'stat_date'           => $statDate,
                'collection_stats'    => $collectionStats,
                'disbursement_stats'  => $disbursementStats,
                'error'               => $e->getMessage(),
                'trace'               => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 验证账户ID的有效性
     */
    private function validateAccountId(int $accountId, array $accountInfo, string $statDate): bool
    {
        if (!$accountId || $accountId <= 0) {
            Log::warning("账户ID无效，跳过统计", [
                'account_info' => $accountInfo,
                'stat_date' => $statDate
            ]);
            return false;
        }
        return true;
    }

    /**
     * 构建账户ID映射
     */
    private function buildAccountIdMapping(string $accountType, int $accountId): array
    {
        if ($accountType === 'channel') {
            // 上游渠道账户
            return [$accountId, 0];
        } else {
            // 银行账户
            return [0, $accountId];
        }
    }

    /**
     * 计算成功率
     */
    private function calculateSuccessRates(array $collectionStats, array $disbursementStats): array
    {
        // 计算收款成功率
        $collectionTotalCount = $collectionStats['success_count'] + $collectionStats['failure_count'];
        $collectionSuccessRate = $collectionTotalCount > 0
            ? round(($collectionStats['success_count'] / $collectionTotalCount) * 100, 2)
            : 0;

        // 计算付款成功率
        $disbursementTotalCount = $disbursementStats['success_count'] + $disbursementStats['failure_count'];
        $disbursementSuccessRate = $disbursementTotalCount > 0
            ? round(($disbursementStats['success_count'] / $disbursementTotalCount) * 100, 2)
            : 0;

        return [$collectionSuccessRate, $disbursementSuccessRate];
    }

    /**
     * 构建统计数据数组
     */
    private function buildStatsData(array $params): array
    {
        $data = [
            'channel_account_id'              => $params['channel_account_id'],
            'bank_account_id'                 => $params['bank_account_id'],
            'channel_id'                      => $params['channel_id'],
            'stat_date'                       => $params['stat_date'],
            'collection_transaction_count'    => $params['collection_stats']['transaction_count'],
            'disbursement_transaction_count'  => $params['disbursement_stats']['transaction_count'],
            'collection_success_count'        => $params['collection_stats']['success_count'],
            'collection_failure_count'        => $params['collection_stats']['failure_count'],
            'disbursement_success_count'      => $params['disbursement_stats']['success_count'],
            'disbursement_failure_count'      => $params['disbursement_stats']['failure_count'],
            'receipt_amount'                  => $params['collection_stats']['amount_total'],
            'payment_amount'                  => $params['disbursement_stats']['amount_total'],
            'collection_success_rate'         => $params['collection_success_rate'],
            'disbursement_success_rate'       => $params['disbursement_success_rate'],
            'collection_avg_process_time'     => (int)($params['collection_stats']['avg_process_time'] ?? 0),
            'disbursement_avg_process_time'   => (int)($params['disbursement_stats']['avg_process_time'] ?? 0),
            'updated_at'                      => Carbon::now(),
        ];

        // 检查是否是当日统计（实时统计）
        $isToday = $params['stat_date'] === Carbon::today()->format('Y-m-d');

        // 只有当日统计才计算限额状态
        if ($isToday) {
            $data['limit_status'] = $this->calculateLimitStatus($params['account_type'], $params['account_id']);
        }

        return $data;
    }

    /**
     * 保存统计记录
     */
    private function saveStatsRecord(int $channelAccountId, int $bankAccountId, string $statDate, array $data): object
    {
        $conditions = [
            'channel_account_id' => $channelAccountId,
            'bank_account_id'    => $bankAccountId,
            'stat_date'          => $statDate
        ];

        return $this->repository->updateOrCreateStats($conditions, $data);
    }

    /**
     * 计算限额状态
     * 0正常：所有指标都没有超过限制配置
     * 1部分限额：部分指标超过了限制配置
     * 2全部限额：所有指标都超过限制配置
     */
    private function calculateLimitStatus(string $accountType, int $accountId): int
    {
        try {
            // 获取账户配置
            $account = $this->getAccountFromCache($accountType, $accountId);

            if (!$account) {
                Log::warning("账户不存在", ['account_type' => $accountType, 'account_id' => $accountId]);
                return self::LIMIT_STATUS_FULL; // 完全限额（安全考虑）
            }

            // 获取实时数据
            $todayReceiptAmount = $account->today_receipt_amount ?? 0;
            $todayReceiptCount = $account->today_receipt_count ?? 0;
            $todayPaymentAmount = $account->today_payment_amount ?? 0;
            $todayPaymentCount = $account->today_payment_count ?? 0;
            $usedQuota = $account->used_quota ?? 0;

            $limitChecks = $this->buildLimitChecks($account, [
                'today_receipt_amount' => $todayReceiptAmount,
                'today_receipt_count'  => $todayReceiptCount,
                'today_payment_amount' => $todayPaymentAmount,
                'today_payment_count'  => $todayPaymentCount,
                'used_quota'           => $usedQuota
            ]);

            return $this->determineLimitStatus($limitChecks, $accountType, $accountId, [
                'today_receipt_amount' => $todayReceiptAmount,
                'today_receipt_count'  => $todayReceiptCount,
                'today_payment_amount' => $todayPaymentAmount,
                'today_payment_count'  => $todayPaymentCount,
                'used_quota'           => $usedQuota
            ]);

        } catch (Throwable $e) {
            Log::error("计算限额状态失败", [
                'account_type' => $accountType,
                'account_id'   => $accountId,
                'trace'        => $e->getTraceAsString()
            ]);
            return self::LIMIT_STATUS_FULL; // 发生错误时返回完全限额（安全考虑）
        }
    }

    /**
     * 构建限额检查数组
     */
    private function buildLimitChecks(object $account, array $todayData): array
    {
        $limitChecks = [];

        // 检查收款金额限制（配置为0表示不限制）
        if (isset($account->daily_max_receipt) && $account->daily_max_receipt > 0) {
            $limitChecks['receipt_amount'] = $todayData['today_receipt_amount'] >= $account->daily_max_receipt;
        }

        // 检查收款次数限制（配置为0表示不限制）
        if (isset($account->daily_max_receipt_count) && $account->daily_max_receipt_count > 0) {
            $limitChecks['receipt_count'] = $todayData['today_receipt_count'] >= $account->daily_max_receipt_count;
        }

        // 检查付款金额限制（配置为0表示不限制）
        if (isset($account->daily_max_payment) && $account->daily_max_payment > 0) {
            $limitChecks['payment_amount'] = $todayData['today_payment_amount'] >= $account->daily_max_payment;
        }

        // 检查付款次数限制（配置为0表示不限制）
        if (isset($account->daily_max_payment_count) && $account->daily_max_payment_count > 0) {
            $limitChecks['payment_count'] = $todayData['today_payment_count'] >= $account->daily_max_payment_count;
        }

        // 检查使用额度限制
        if (isset($account->limit_quota) && $account->limit_quota > 0) {
            $limitChecks['used_quota'] = $todayData['used_quota'] >= $account->limit_quota;
        }

        return $limitChecks;
    }

    /**
     * 根据限制检查结果确定限额状态
     */
    private function determineLimitStatus(array $limitChecks, string $accountType, int $accountId, array $logData): int
    {
        // 如果没有配置任何限制，返回正常
        if (empty($limitChecks)) {
            return self::LIMIT_STATUS_NORMAL;
        }

        $exceededChecks = array_filter($limitChecks);
        $totalChecks = count($limitChecks);
        $exceededCount = count($exceededChecks);

        // 记录限额状态变化日志
        if ($exceededCount > 0) {
            Log::info("账户限额状态检查", array_merge([
                'account_type'    => $accountType,
                'account_id'      => $accountId,
                'exceeded_checks' => array_keys($exceededChecks),
                'total_checks'    => $totalChecks,
                'exceeded_count'  => $exceededCount
            ], $logData));
        }

        // 根据超限指标数量判断限额状态
        if ($exceededCount == 0) {
            return self::LIMIT_STATUS_NORMAL; // 正常
        } elseif ($exceededCount == $totalChecks) {
            return self::LIMIT_STATUS_FULL; // 全部限额
        } else {
            return self::LIMIT_STATUS_PARTIAL; // 部分限额
        }
    }

    /**
     * 从缓存获取账户配置
     */
    private function getAccountFromCache(string $accountType, int $accountId): ?object
    {
        $cacheKey = $accountType . '_' . $accountId;

        if (!isset($this->accountCache[$cacheKey])) {
            if ($accountType === 'channel') {
                $this->accountCache[$cacheKey] = $this->channelAccountRepository->findById($accountId);
            } else {
                $this->accountCache[$cacheKey] = $this->bankAccountRepository->findById($accountId);
            }
        }

        return $this->accountCache[$cacheKey];
    }
}