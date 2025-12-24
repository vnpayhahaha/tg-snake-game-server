<?php

namespace app\event;

use app\constants\DisbursementOrder;
use app\constants\TenantAccount;
use app\model\ModelDisbursementOrderUpstreamCreateQueue;
use app\model\ModelTenant;
use app\model\ModelTenantAccount;
use app\service\DisbursementOrderService;
use support\Container;
use support\Log;
use Webman\Event\Event;

class TenantEvent
{
    public function Created(ModelTenant $model): void
    {
        $accountModel = Container::make(ModelTenantAccount::class);
        var_dump('TenantCreated  event==', $model);
        $insertAccount = $accountModel->insert([
            [
                'tenant_id'         => $model->tenant_id,
                'account_id'        => TenantAccount::ACCOUNT_ID_PREFIX_RECEIVE . $model->tenant_id,
                'balance_available' => 0,
                'balance_frozen'    => 0,
                'account_type'      => TenantAccount::ACCOUNT_TYPE_RECEIVE,
                'version'           => 1,
            ],
            [
                'tenant_id'         => $model->tenant_id,
                'account_id'        => TenantAccount::ACCOUNT_ID_PREFIX_PAY . $model->tenant_id,
                'balance_available' => 0,
                'balance_frozen'    => 0,
                'account_type'      => TenantAccount::ACCOUNT_TYPE_PAY,
                'version'           => 1,
            ]
        ]);

        var_dump('insertAccount==', $insertAccount);
    }

    // auto_assign
    public function AutoAssign(array $params): void
    {
//        string $tenant_id, int $disbursement_order_id
        $tenant_id = $params['tenant_id'] ?? '';
        $disbursement_order_id = $params['order_id'] ?? 0;
        /** @var ModelTenant $tenantModel */
        $tenantModel = Container::make(ModelTenant::class);
        $tenant = $tenantModel->where('tenant_id', $tenant_id)->first();
        if (!$tenant) {
            return;
        }
        /** @var ModelDisbursementOrderUpstreamCreateQueue $upstreamCreateQueueModel */
        $upstreamCreateQueueModel = Container::make(ModelDisbursementOrderUpstreamCreateQueue::class);
        if ($tenant->auto_assign_enabled) {
            $isOk = false;
            $payment_assign_items = $tenant->payment_assign_items;
            foreach ($payment_assign_items as $channel_account_id) {
                // 查询disbursement_order_upstream_create_queue 是否存在  disbursement_order_id tenant_id  channel_account_id
                $upstreamCreateQueue = $upstreamCreateQueueModel
                    ->where('disbursement_order_id', $disbursement_order_id)
                    ->where('tenant_id', $tenant_id)
                    ->where('channel_account_id', $channel_account_id)
                    ->first();
                if ($upstreamCreateQueue) {
                    continue;
                }
                var_dump('TenantAutoAssign 9999  event==', $tenant_id, $channel_account_id);
                Log::info("TenantAutoAssign 999` event==", ['$tenant_id' => $tenant_id, '$channel_account_id' => $channel_account_id]);
                // 自动分配 DisbursementOrderService
                /** @var DisbursementOrderService $disbursementOrderService */
                $disbursementOrderService = Container::make(DisbursementOrderService::class);
                $isOk = $disbursementOrderService->autoDistribute($disbursement_order_id, $channel_account_id);
                var_dump('TenantAutoAssign 9999  event==', $tenant_id, $channel_account_id, $isOk);
                if ($isOk) {
                    $disbursementOrderService->addToUpstreamCreateQueue([$disbursement_order_id]);
                }
            }
            if (!$isOk) {
                // 分配都失败
                /** @var DisbursementOrderService $disbursementOrderService */
                $disbursementOrderService = Container::make(DisbursementOrderService::class);
                var_dump('----------自动分配失败-------');
                $isUpdate = $disbursementOrderService->repository->getQuery()
                    ->where('id', $disbursement_order_id)
                    ->where('status', DisbursementOrder::STATUS_CREATED)
                    ->update([
                        'status'        => DisbursementOrder::STATUS_SUSPEND,
                        'error_code'    => 'ERROR_CODE_AUTO_ASSIGN_FAIL',
                        'error_message' => 'Automatic allocation failed',
                    ]);
                var_dump('TenantAutoAssign=$disbursementOrderService==event=update=', $tenant_id, $isUpdate);
                if ($isUpdate) {
                    Event::dispatch('disbursement-order-status-records', [
                        'order_id' => $disbursement_order_id,
                        'status'   => DisbursementOrder::STATUS_SUSPEND,
                        'desc_cn'  => '自动分配失败',
                        'desc_en'  => 'Auto-assignment failed',
                        'remark'   => json_encode($payment_assign_items, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        }
        var_dump('TenantAutoAssign  event==', $tenant_id);
    }
}
