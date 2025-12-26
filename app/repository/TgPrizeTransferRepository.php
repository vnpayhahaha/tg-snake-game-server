<?php

namespace app\repository;

use app\constants\TgPrizeTransfer as TransferConst;
use app\model\ModelTgPrizeTransfer;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgPrizeTransferRepository.
 * @extends IRepository<ModelTgPrizeTransfer>
 */
class TgPrizeTransferRepository extends IRepository
{
    #[Inject]
    protected ModelTgPrizeTransfer $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['prize_record_id']) && filled($params['prize_record_id'])) {
            $query->where('prize_record_id', $params['prize_record_id']);
        }

        if (isset($params['prize_serial_no']) && filled($params['prize_serial_no'])) {
            $query->where('prize_serial_no', $params['prize_serial_no']);
        }

        if (isset($params['node_id']) && filled($params['node_id'])) {
            $query->where('node_id', $params['node_id']);
        }

        if (isset($params['to_address']) && filled($params['to_address'])) {
            $query->where('to_address', $params['to_address']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['tx_hash']) && filled($params['tx_hash'])) {
            $query->where('tx_hash', $params['tx_hash']);
        }

        return $query;
    }

    /**
     * 根据中奖记录ID获取转账列表
     */
    public function getByPrizeRecordId(int $prizeRecordId): Collection
    {
        return $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->orderBy('id')
            ->get();
    }

    /**
     * 根据节点ID获取转账记录
     */
    public function getByNodeId(int $nodeId): Collection
    {
        return $this->model::query()
            ->where('node_id', $nodeId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * 根据玩家地址获取转账记录
     */
    public function getByPlayerAddress(string $address, int $limit = 10): Collection
    {
        return $this->model::query()
            ->where('to_address', $address)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据交易哈希查询
     */
    public function getByTxHash(string $txHash): ?ModelTgPrizeTransfer
    {
        return $this->model::query()
            ->where('tx_hash', $txHash)
            ->first();
    }

    /**
     * 获取待处理的转账
     */
    public function getPendingTransfers(int $limit = 100): Collection
    {
        return $this->model::query()
            ->where('status', TransferConst::STATUS_PENDING)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取处理中的转账
     */
    public function getProcessingTransfers(): Collection
    {
        return $this->model::query()
            ->where('status', TransferConst::STATUS_PROCESSING)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * 获取失败的转账（需要重试）
     */
    public function getFailedTransfers(int $maxRetryCount = 3): Collection
    {
        return $this->model::query()
            ->where('status', TransferConst::STATUS_FAILED)
            ->where('retry_count', '<', $maxRetryCount)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * 更新转账状态
     */
    public function updateStatus(int $id, int $status, string $txHash = null, string $errorMessage = null): bool
    {
        $data = ['status' => $status];

        if ($txHash !== null) {
            $data['tx_hash'] = $txHash;
        }

        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }

        return (bool)$this->model::query()
            ->whereKey($id)
            ->update($data);
    }

    /**
     * 增加重试次数
     */
    public function incrementRetryCount(int $id, string $errorMessage = null): bool
    {
        $data = [];

        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }

        return (bool)$this->model::query()
            ->whereKey($id)
            ->increment('retry_count', 1, $data);
    }

    /**
     * 批量更新状态
     */
    public function batchUpdateStatus(array $ids, int $status): int
    {
        return $this->model::query()
            ->whereIn('id', $ids)
            ->update(['status' => $status]);
    }

    /**
     * 统计转账数据
     */
    public function getTransferStatistics(int $prizeRecordId): array
    {
        $total = $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->count();

        $success = $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->where('status', TransferConst::STATUS_SUCCESS)
            ->count();

        $failed = $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->where('status', TransferConst::STATUS_FAILED)
            ->count();

        $pending = $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->whereIn('status', [TransferConst::STATUS_PENDING, TransferConst::STATUS_PROCESSING])
            ->count();

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'pending' => $pending,
        ];
    }

    /**
     * 检查中奖记录是否全部转账完成
     */
    public function isAllTransfersCompleted(int $prizeRecordId): bool
    {
        $pendingCount = $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->whereIn('status', [TransferConst::STATUS_PENDING, TransferConst::STATUS_PROCESSING])
            ->count();

        return $pendingCount === 0;
    }

    /**
     * 获取成功转账总金额
     */
    public function getSuccessTransferAmount(int $prizeRecordId): float
    {
        return $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->where('status', TransferConst::STATUS_SUCCESS)
            ->sum('amount');
    }

    /**
     * 根据中奖ID获取转账列表（别名方法）
     */
    public function getByPrizeId(int $prizeRecordId): Collection
    {
        return $this->getByPrizeRecordId($prizeRecordId);
    }
}
