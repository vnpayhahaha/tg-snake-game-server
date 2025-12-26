<?php

namespace app\service;

use app\constants\TgPrizeTransfer as TransferConst;
use app\repository\TgPrizeTransferRepository;
use app\repository\TgPrizeRecordRepository;
use DI\Attribute\Inject;
use support\Db;
use support\Log;

/**
 * 奖金转账服务
 * @extends BaseService
 */
class TgPrizeTransferService extends BaseService
{
    #[Inject]
    public TgPrizeTransferRepository $repository;

    #[Inject]
    protected TgPrizeRecordRepository $prizeRecordRepository;

    /**
     * 创建转账记录
     */
    public function createTransfer(array $data)
    {
        return $this->repository->create([
            'prize_record_id' => $data['prize_record_id'],
            'prize_serial_no' => $data['prize_serial_no'],
            'node_id' => $data['node_id'],
            'to_address' => $data['to_address'],
            'amount' => $data['amount'],
            'tx_hash' => null,
            'status' => TransferConst::STATUS_PENDING,
            'retry_count' => 0,
            'error_message' => null,
        ]);
    }

    /**
     * 批量创建转账记录
     */
    public function batchCreateTransfers(int $prizeRecordId, string $prizeSerialNo, array $transfers): array
    {
        try {
            Db::beginTransaction();

            $created = [];
            foreach ($transfers as $transfer) {
                $record = $this->createTransfer([
                    'prize_record_id' => $prizeRecordId,
                    'prize_serial_no' => $prizeSerialNo,
                    'node_id' => $transfer['node_id'],
                    'to_address' => $transfer['to_address'],
                    'amount' => $transfer['amount'],
                ]);
                $created[] = $record;
            }

            Db::commit();

            return [
                'success' => true,
                'count' => count($created),
                'transfers' => $created,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('批量创建转账记录失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 更新转账状态
     */
    public function updateStatus(int $id, int $status, string $txHash = null, string $errorMessage = null): bool
    {
        return $this->repository->updateStatus($id, $status, $txHash, $errorMessage);
    }

    /**
     * 标记转账成功
     */
    public function markAsSuccess(int $id, string $txHash = null): array
    {
        try {
            $result = $this->repository->updateStatus($id, TransferConst::STATUS_SUCCESS, $txHash);
            return [
                'success' => $result,
                'message' => $result ? '标记成功' : '标记失败',
            ];
        } catch (\Exception $e) {
            Log::error('标记转账成功失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 标记转账失败并增加重试次数
     */
    public function markAsFailed(int $id, string $errorMessage): array
    {
        try {
            $result = $this->repository->incrementRetryCount($id, $errorMessage);
            return [
                'success' => $result,
                'message' => $result ? '标记失败成功' : '标记失败失败',
            ];
        } catch (\Exception $e) {
            Log::error('标记转账失败失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取待处理的转账
     */
    public function getPendingTransfers(int $limit = 100)
    {
        return $this->repository->getPendingTransfers($limit);
    }

    /**
     * 获取处理中的转账
     */
    public function getProcessingTransfers()
    {
        return $this->repository->getProcessingTransfers();
    }

    /**
     * 获取失败需要重试的转账
     */
    public function getFailedTransfers(int $maxRetryCount = TransferConst::MAX_RETRY_COUNT)
    {
        return $this->repository->getFailedTransfers($maxRetryCount);
    }

    /**
     * 根据中奖记录ID获取转账列表
     */
    public function getByPrizeRecordId(int $prizeRecordId)
    {
        return $this->repository->getByPrizeRecordId($prizeRecordId);
    }

    /**
     * 根据玩家地址获取转账记录
     */
    public function getByPlayerAddress(string $address, int $limit = 10)
    {
        return $this->repository->getByPlayerAddress($address, $limit);
    }

    /**
     * 统计转账数据
     */
    public function getTransferStatistics(int $prizeRecordId): array
    {
        return $this->repository->getTransferStatistics($prizeRecordId);
    }

    /**
     * 检查中奖记录是否全部转账完成
     */
    public function isAllTransfersCompleted(int $prizeRecordId): bool
    {
        return $this->repository->isAllTransfersCompleted($prizeRecordId);
    }

    /**
     * 获取成功转账总金额
     */
    public function getSuccessTransferAmount(int $prizeRecordId): float
    {
        return $this->repository->getSuccessTransferAmount($prizeRecordId);
    }

    /**
     * 批量更新状态
     */
    public function batchUpdateStatus(array $ids, int $status): int
    {
        return $this->repository->batchUpdateStatus($ids, $status);
    }

    /**
     * 根据节点ID获取转账
     */
    public function getByNodeId(int $nodeId)
    {
        return Db::table('tg_prize_transfer')
            ->where('node_id', $nodeId)
            ->first();
    }

    /**
     * 根据交易哈希获取转账
     */
    public function getByTxHash(string $txHash)
    {
        return Db::table('tg_prize_transfer')
            ->where('tx_hash', $txHash)
            ->first();
    }

    /**
     * 获取导出数据
     */
    public function getExportData(array $params, int $limit = 10000)
    {
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 重试转账
     */
    public function retryTransfer(int $id): array
    {
        try {
            $transfer = $this->repository->findById($id);
            if (!$transfer) {
                return [
                    'success' => false,
                    'message' => '转账记录不存在',
                ];
            }

            if ($transfer->status == TransferConst::STATUS_SUCCESS) {
                return [
                    'success' => false,
                    'message' => '转账已成功，无需重试',
                ];
            }

            if ($transfer->retry_count >= TransferConst::MAX_RETRY_COUNT) {
                return [
                    'success' => false,
                    'message' => '已达到最大重试次数',
                ];
            }

            // 重置状态为待处理
            $this->repository->updateStatus($id, TransferConst::STATUS_PENDING);

            return [
                'success' => true,
                'message' => '已重新加入处理队列',
            ];
        } catch (\Exception $e) {
            Log::error('重试转账失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 批量重试转账
     */
    public function batchRetryTransfers(array $transferIds): array
    {
        try {
            Db::beginTransaction();

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($transferIds as $id) {
                $result = $this->retryTransfer($id);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = [
                        'id' => $id,
                        'error' => $result['message'],
                    ];
                }
            }

            Db::commit();

            return [
                'success' => true,
                'total' => count($transferIds),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('批量重试转账失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 根据中奖ID获取转账（别名）
     */
    public function getByPrizeId(int $prizeRecordId)
    {
        return $this->getByPrizeRecordId($prizeRecordId);
    }

    /**
     * 根据地址获取转账（别名）
     */
    public function getByAddress(string $address, int $limit = 50)
    {
        return $this->getByPlayerAddress($address, $limit);
    }
}
