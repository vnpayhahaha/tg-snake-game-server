<?php

namespace app\repository;

use app\constants\DisbursementOrderUpstreamCreateQueue;
use app\model\ModelDisbursementOrderUpstreamCreateQueue;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DisbursementOrderUpstreamCreateQueueRepository.
 * @extends IRepository<ModelDisbursementOrderUpstreamCreateQueue>
 */
class DisbursementOrderUpstreamCreateQueueRepository extends IRepository
{
    #[Inject]
    protected ModelDisbursementOrderUpstreamCreateQueue $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['platform_order_no']) && filled($params['platform_order_no'])) {
            $query->where('platform_order_no', $params['platform_order_no']);
        }
        if (isset($params['disbursement_order_id']) && filled($params['disbursement_order_id'])) {
            $query->where('disbursement_order_id', $params['disbursement_order_id']);
        }
        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }
        if (isset($params['app_id']) && filled($params['app_id'])) {
            $query->where('app_id', $params['app_id']);
        }
        if (isset($params['channel_account_id']) && filled($params['channel_account_id'])) {
            $query->where('channel_account_id', $params['channel_account_id']);
        }
        if (isset($params['process_status']) && filled($params['process_status'])) {
            $query->where('process_status', $params['process_status']);
        }
        if (isset($params['payment_type']) && filled($params['payment_type'])) {
            $query->where('payment_type', $params['payment_type']);
        }
        if (isset($params['payee_account_no']) && filled($params['payee_account_no'])) {
            $query->where('payee_account_no', $params['payee_account_no']);
        }
        if (isset($params['upstream_order_no']) && filled($params['upstream_order_no'])) {
            $query->where('upstream_order_no', $params['upstream_order_no']);
        }
        if (isset($params['error_code']) && filled($params['error_code'])) {
            $query->where('error_code', $params['error_code']);
        }

        // 时间范围查询
        if (isset($params['created_start']) && filled($params['created_start'])) {
            $query->where('created_at', '>=', $params['created_start']);
        }
        if (isset($params['created_end']) && filled($params['created_end'])) {
            $query->where('created_at', '<=', $params['created_end']);
        }
        if (isset($params['next_retry_start']) && filled($params['next_retry_start'])) {
            $query->where('next_retry_time', '>=', $params['next_retry_start']);
        }
        if (isset($params['next_retry_end']) && filled($params['next_retry_end'])) {
            $query->where('next_retry_time', '<=', $params['next_retry_end']);
        }

        return $query;
    }

    /**
     * 获取待处理的队列项目
     * @param int $limit
     * @return Collection
     */
    public function getPendingItems(int $limit = 10): Collection
    {
        return $this->model->where('process_status', DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_WAIT)
            ->where(function ($query) {
                $query->whereNull('next_retry_time')
                    ->orWhere('next_retry_time', '<=', now());
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据平台订单号查找队列项目
     * @param string $platformOrderNo
     * @return ModelDisbursementOrderUpstreamCreateQueue|null
     */
    public function findByPlatformOrderNo(string $platformOrderNo): ?ModelDisbursementOrderUpstreamCreateQueue
    {
        return $this->model->where('platform_order_no', $platformOrderNo)->first();
    }

    /**
     * 根据代付订单ID查找队列项目
     * @param int $disbursementOrderId
     * @return ModelDisbursementOrderUpstreamCreateQueue|null
     */
    public function findByDisbursementOrderId(int $disbursementOrderId): ?ModelDisbursementOrderUpstreamCreateQueue
    {
        return $this->model->where('disbursement_order_id', $disbursementOrderId)->first();
    }

    /**
     * 更新处理状态
     * @param int $id
     * @param int $status
     * @param array $additionalData
     * @return bool
     */
    public function updateProcessStatus(int $id, int $status, array $additionalData = []): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        $data = array_merge([
            'process_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'lock_version' => $item->lock_version + 1,
        ], $additionalData);

        if ($status === DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_SUCCESS ||
            $status === DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_FAIL) { // 成功或失败时设置处理完成时间
            $data['processed_at'] = date('Y-m-d H:i:s');
        }

        // 使用乐观锁更新
        return $this->model->where('id', $id)
            ->where('lock_version', $item->lock_version)
            ->update($data) > 0;
    }

    /**
     * 增加重试次数并设置下次重试时间
     * @param int $id
     * @param string|null $errorCode
     * @param string|null $errorMessage
     * @return bool
     */
    public function incrementRetryCount(int $id, ?string $errorCode = null, ?string $errorMessage = null): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        $retryCount = $item->retry_count + 1;
        $data = [
            'retry_count' => $retryCount,
            'updated_at' => date('Y-m-d H:i:s'),
            'lock_version' => $item->lock_version + 1,
        ];

        if ($errorCode) {
            $data['error_code'] = $errorCode;
        }
        if ($errorMessage) {
            $data['error_message'] = $errorMessage;
        }

        // 如果超过最大重试次数，标记为失败
        if ($retryCount >= $item->max_retry_count) {
            $data['process_status'] = DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_FAIL;
            $data['processed_at'] = date('Y-m-d H:i:s');
        } else {
            // 设置下次重试时间（使用常量定义的间隔）
            $interval = DisbursementOrderUpstreamCreateQueue::RETRY_INTERVALS[$retryCount] ?? 16;
            $data['next_retry_time'] = date('Y-m-d H:i:s', strtotime("+{$interval} minutes"));
        }

        // 使用乐观锁更新
        return $this->model->where('id', $id)
            ->where('lock_version', $item->lock_version)
            ->update($data) > 0;
    }

    /**
     * 获取失败的队列项目
     * @param array $params
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getFailedItems(array $params = []): Collection|\Illuminate\Support\Collection
    {
        $params['process_status'] = DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_FAIL;
        return $this->list($params);
    }

    /**
     * 获取成功的队列项目
     * @param array $params
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getSuccessItems(array $params = []): Collection|\Illuminate\Support\Collection
    {
        $params['process_status'] = DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_SUCCESS;
        return $this->list($params);
    }

    /**
     * 重置队列项目状态用于重新处理
     * @param int $id
     * @return bool
     */
    public function resetForRetry(int $id): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        $data = [
            'process_status' => DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_WAIT,
            'retry_count' => 0,
            'next_retry_time' => null,
            'error_code' => null,
            'error_message' => null,
            'processed_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'lock_version' => $item->lock_version + 1,
        ];

        // 使用乐观锁更新
        return $this->model->where('id', $id)
            ->where('lock_version', $item->lock_version)
            ->update($data) > 0;
    }
}