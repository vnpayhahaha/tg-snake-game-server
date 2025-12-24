<?php

namespace app\repository;

use app\constants\TransactionRecord;
use app\model\ModelTenantAccount;
use app\model\ModelTransactionRecord;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Webman\Event\Event;

/**
 * Class TransactionRecordRepository.
 * @extends IRepository<ModelTransactionRecord>
 */
class TransactionRecordRepository extends IRepository
{
    #[Inject]
    protected ModelTransactionRecord $model;

    // D0 创建完成直接走队列
    // D/T 可能面临修改执行时间，创建完成不走队列，用定时任务查询预计执行时间执行

    // 业务交易类型：
    //# 基础交易类型 (1XX)
    //100: 收款
    //110: 付款
    //120: 转账
    //
    //# 退款相关 (2XX)
    //200: 收款退款
    //210: 付款退款
    //
    //# 手续费类 (3XX)
    //300: 收款手续费
    //310: 付款手续费
    //320: 转账手续费
    //
    //# 资金调整 (4XX)
    //400: 资金调增（人工）
    //410: 资金调减（人工）
    //420: 冻结资金
    //430: 解冻资金
    //
    //# 特殊交易 (9XX)
    //900: 冲正交易
    //910: 差错调整

    // 资金调整
    public function adjustFunds(int $admin_id, string $admin_username, ModelTenantAccount $account, float $amount, float $fee_amount = 0, string $remark = ''): bool
    {

        return (bool)$this->model::query()->create([
            'tenant_account_id'        => $account->id,
            'account_id'               => $account->account_id,
            'tenant_id'                => $account->tenant_id,
            'amount'                   => $amount,
            'fee_amount'               => $fee_amount,
            'net_amount'               => bcsub((string)$amount, (string)$fee_amount, 4),
            'account_type'             => $account->account_type,
            'transaction_type'         => $amount >= 0 ? TransactionRecord::TYPE_MANUAL_ADD : TransactionRecord::TYPE_MANUAL_SUB,
            'settlement_delay_mode'    => TransactionRecord::SETTLEMENT_DELAY_MODE_D0,
            'expected_settlement_time' => date('Y-m-d H:i:s'),
            'counterparty'             => '',
            'order_no'                 => $admin_username,
            'order_id'                 => $admin_id,
            'remark'                   => $remark,
        ]);
    }

    // 冻结/解冻资金
    public function freezeFunds(int $admin_id, string $admin_username, ModelTenantAccount $account, float $amount, float $fee_amount = 0, string $remark = ''): bool
    {
        return (bool)$this->model::query()->create([
            'tenant_account_id'        => $account->id,
            'account_id'               => $account->account_id,
            'tenant_id'                => $account->tenant_id,
            'amount'                   => $amount,
            'fee_amount'               => $fee_amount,
            'net_amount'               => bcsub((string)$amount, (string)$fee_amount, 4),
            'account_type'             => $account->account_type,
            'transaction_type'         => $amount >= 0 ? TransactionRecord::TYPE_FREEZE : TransactionRecord::TYPE_UNFREEZE,
            'settlement_delay_mode'    => TransactionRecord::SETTLEMENT_DELAY_MODE_D0,
            'expected_settlement_time' => date('Y-m-d H:i:s'),
            'counterparty'             => '',
            'order_no'                 => $admin_username,
            'order_id'                 => $admin_id,
            'remark'                   => $remark,
        ]);
    }

    // TYPE_ORDER_TRANSACTION
    public function orderTransaction(int $order_id, string $platform_order_no, ModelTenantAccount $account, float $amount, float $fee_amount = 0, int $settlement_delay_mode = 1, int $settlement_delay_days = 0, string $remark = '', $transaction_type = TransactionRecord::TYPE_ORDER_TRANSACTION): \Illuminate\Database\Eloquent\Model|ModelTransactionRecord
    {
//        $settlement_delay_mode = $account['tenant']['settlement_delay_mode'] ?? 1;
//        $settlement_delay_days = $account['tenant']['settlement_delay_days'] ?? 0;

        try {
            $expected_settlement_time = calculateSettlementDate($settlement_delay_mode, $settlement_delay_days);
        } catch (\Throwable $ex) {
            var_dump('====待入账时间计算失败====', $ex);
            throw new \Exception('The calculation of the estimated settlement time failed:' . $ex->getMessage());
        }
        return $this->model::query()->create([
            'tenant_account_id'        => $account->id,
            'account_id'               => $account->account_id,
            'tenant_id'                => $account->tenant_id,
            'amount'                   => $amount,
            'fee_amount'               => $fee_amount,
            'net_amount'               => bcadd((string)$amount, (string)$fee_amount, 4),
            'account_type'             => $account->account_type,
            'transaction_type'         => $transaction_type,
            'settlement_delay_mode'    => $settlement_delay_mode,
            'settlement_delay_days'    => $settlement_delay_days,
            'expected_settlement_time' => $expected_settlement_time->format('Y-m-d H:i:s'),
            'counterparty'             => '',
            'order_no'                 => $platform_order_no,
            'order_id'                 => $order_id,
            'remark'                   => $remark,
        ]);
    }

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['transaction_no']) && filled($params['transaction_no'])) {
            $query->where('transaction_no', $params['transaction_no']);
        }

        if (isset($params['account_type']) && filled($params['account_type'])) {
            $query->where('account_type', $params['account_type']);
        }

        if (isset($params['transaction_type']) && filled($params['transaction_type'])) {
            $query->where('transaction_type', $params['transaction_type']);
        }

        if (isset($params['settlement_delay_mode']) && filled($params['settlement_delay_mode'])) {
            $query->where('settlement_delay_mode', $params['settlement_delay_mode']);
        }

        if (isset($params['holiday_adjustment']) && filled($params['holiday_adjustment'])) {
            $query->where('holiday_adjustment', $params['holiday_adjustment']);
        }

        if (isset($params['transaction_status']) && filled($params['transaction_status'])) {
            $query->where('transaction_status', $params['transaction_status']);
        }

        if (isset($params['failed_msg']) && filled($params['failed_msg'])) {
            $query->where('failed_msg', 'like', '%' . $params['failed_msg'] . '%');
        }

        return $query;
    }
}
