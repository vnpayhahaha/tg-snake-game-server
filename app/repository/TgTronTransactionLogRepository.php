<?php

namespace app\repository;

use app\constants\TgTronTransactionLog as TxLogConst;
use app\model\ModelTgTronTransactionLog;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgTronTransactionLogRepository.
 * @extends IRepository<ModelTgTronTransactionLog>
 */
class TgTronTransactionLogRepository extends IRepository
{
    #[Inject]
    protected ModelTgTronTransactionLog $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['group_id']) && filled($params['group_id'])) {
            $query->where('group_id', $params['group_id']);
        }

        if (isset($params['tx_hash']) && filled($params['tx_hash'])) {
            $query->where('tx_hash', $params['tx_hash']);
        }

        if (isset($params['from_address']) && filled($params['from_address'])) {
            $query->where('from_address', $params['from_address']);
        }

        if (isset($params['to_address']) && filled($params['to_address'])) {
            $query->where('to_address', $params['to_address']);
        }

        if (isset($params['transaction_type']) && filled($params['transaction_type'])) {
            $query->where('transaction_type', $params['transaction_type']);
        }

        if (isset($params['is_valid']) && filled($params['is_valid'])) {
            $query->where('is_valid', $params['is_valid']);
        }

        if (isset($params['processed']) && filled($params['processed'])) {
            $query->where('processed', $params['processed']);
        }

        if (isset($params['block_height_min']) && filled($params['block_height_min'])) {
            $query->where('block_height', '>=', $params['block_height_min']);
        }

        if (isset($params['block_height_max']) && filled($params['block_height_max'])) {
            $query->where('block_height', '<=', $params['block_height_max']);
        }

        return $query;
    }

    /**
     * 根据交易哈希查询（防重复）
     */
    public function getByTxHash(string $txHash): ?ModelTgTronTransactionLog
    {
        return $this->model::query()
            ->where('tx_hash', $txHash)
            ->first();
    }

    /**
     * 检查交易是否已存在
     */
    public function exists(string $txHash): bool
    {
        return $this->model::query()
            ->where('tx_hash', $txHash)
            ->exists();
    }

    /**
     * 获取未处理的有效入账交易
     */
    public function getUnprocessedIncomingTransactions(int $groupId, int $limit = 100): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('transaction_type', TxLogConst::TRANSACTION_TYPE_INCOMING)
            ->where('is_valid', TxLogConst::VALID_YES)
            ->where('processed', TxLogConst::PROCESSED_NO)
            ->orderBy('block_timestamp')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取入账交易
     */
    public function getIncomingTransactions(int $groupId, int $limit = 100): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('transaction_type', TxLogConst::TRANSACTION_TYPE_INCOMING)
            ->orderByDesc('block_timestamp')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取出账交易
     */
    public function getOutgoingTransactions(int $groupId, int $limit = 100): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('transaction_type', TxLogConst::TRANSACTION_TYPE_OUTGOING)
            ->orderByDesc('block_timestamp')
            ->limit($limit)
            ->get();
    }

    /**
     * 标记为已处理
     */
    public function markAsProcessed(int $id): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update(['processed' => TxLogConst::PROCESSED_YES]);
    }

    /**
     * 批量标记为已处理
     */
    public function batchMarkAsProcessed(array $ids): int
    {
        return $this->model::query()
            ->whereIn('id', $ids)
            ->update(['processed' => TxLogConst::PROCESSED_YES]);
    }

    /**
     * 标记为无效交易
     */
    public function markAsInvalid(int $id, string $reason): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'is_valid' => TxLogConst::VALID_NO,
                'invalid_reason' => $reason,
            ]);
    }

    /**
     * 获取指定地址的入账交易
     */
    public function getIncomingByToAddress(int $groupId, string $toAddress, int $limit = 50): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('transaction_type', TxLogConst::TRANSACTION_TYPE_INCOMING)
            ->where('to_address', $toAddress)
            ->where('is_valid', TxLogConst::VALID_YES)
            ->orderByDesc('block_timestamp')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取最新的区块高度
     */
    public function getLatestBlockHeight(int $groupId): ?int
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->max('block_height');
    }

    /**
     * 获取最新的区块时间戳（秒）
     */
    public function getLatestBlockTimestamp(int $groupId): ?int
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->max('block_timestamp');
    }

    /**
     * 统计交易数据
     */
    public function getTransactionStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        $query = $this->model::query()
            ->where('group_id', $groupId);

        if ($dateStart) {
            $query->whereDate('created_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->whereDate('created_at', '<=', $dateEnd);
        }

        $incomingStats = (clone $query)
            ->where('transaction_type', TxLogConst::TRANSACTION_TYPE_INCOMING)
            ->where('is_valid', TxLogConst::VALID_YES)
            ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount')
            ->first();

        $outgoingStats = (clone $query)
            ->where('transaction_type', TxLogConst::TRANSACTION_TYPE_OUTGOING)
            ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount')
            ->first();

        return [
            'incoming' => [
                'count' => $incomingStats->count ?? 0,
                'total_amount' => $incomingStats->total_amount ?? 0,
            ],
            'outgoing' => [
                'count' => $outgoingStats->count ?? 0,
                'total_amount' => $outgoingStats->total_amount ?? 0,
            ],
        ];
    }

    /**
     * 获取指定区块高度范围的交易
     */
    public function getByBlockHeightRange(int $groupId, int $minHeight, int $maxHeight): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->whereBetween('block_height', [$minHeight, $maxHeight])
            ->orderBy('block_height')
            ->get();
    }

    /**
     * 清理旧的交易日志（超过指定天数）
     */
    public function cleanOldLogs(int $daysAgo = 90): int
    {
        return $this->model::query()
            ->where('created_at', '<', now()->subDays($daysAgo))
            ->delete();
    }

    /**
     * 根据交易哈希查询（别名方法）
     */
    public function findByTxHash(string $txHash): ?ModelTgTronTransactionLog
    {
        return $this->getByTxHash($txHash);
    }

    /**
     * 根据地址获取交易
     */
    public function getByAddress(string $address, string $direction = null, int $limit = 50): Collection
    {
        $query = $this->model::query();

        if ($direction === 'from') {
            $query->where('from_address', $address);
        } elseif ($direction === 'to') {
            $query->where('to_address', $address);
        } else {
            $query->where(function ($q) use ($address) {
                $q->where('from_address', $address)
                  ->orWhere('to_address', $address);
            });
        }

        return $query->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
