<?php

namespace app\service;

use app\constants\DisbursementOrder;
use app\constants\DisbursementOrderUpstreamCreateQueue;
use app\constants\Tenant;
use app\constants\TenantAccount;
use app\constants\TenantNotificationQueue;
use app\constants\TransactionRecord;
use app\constants\TransactionVoucher;
use app\exception\BusinessException;
use app\exception\OpenApiException;
use app\lib\annotation\Cacheable;
use app\lib\enum\ResultCode;
use app\lib\LdlExcel\PhpOffice;
use app\model\ModelBankDisbursementDownload;
use app\model\ModelChannelAccount;
use app\model\ModelDisbursementOrder;
use app\model\ModelTenant;
use app\model\ModelTenantApp;
use app\repository\AttachmentRepository;
use app\repository\BankAccountRepository;
use app\repository\BankDisbursementDownloadRepository;
use app\repository\ChannelAccountRepository;
use app\repository\DisbursementOrderRepository;
use app\repository\DisbursementOrderUpstreamCreateQueueRepository;
use app\repository\TenantAccountRepository;
use app\repository\TenantNotificationQueueRepository;
use app\repository\TenantRepository;
use app\repository\TransactionRecordRepository;
use app\repository\TransactionVoucherRepository;
use app\upstream\Handle\TransactionDisbursementOrderFactory;
use Carbon\Carbon;
use DI\Attribute\Inject;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;
use support\Context;
use support\Db;
use support\Log;
use support\Response;
use Webman\Event\Event;
use Webman\Http\Request;
use Webman\RedisQueue\Redis;
use Workerman\Coroutine\Parallel;

class DisbursementOrderService extends BaseService
{
    #[Inject]
    public DisbursementOrderRepository $repository;
    #[Inject]
    protected TenantRepository $tenantRepository;
    #[Inject]
    protected TenantAccountRepository $tenantAccountRepository;
    #[Inject]
    protected BankAccountRepository $bankAccountRepository;
    #[Inject]
    protected ChannelAccountRepository $channelAccountRepository;
    #[Inject]
    protected TransactionVoucherRepository $transactionVoucherRepository;
    #[Inject]
    protected DisbursementOrderUpstreamCreateQueueRepository $upstreamCreateQueueRepository;
    #[Inject]
    protected TransactionRecordRepository $transactionRecordRepository;
    #[Inject]
    protected BankDisbursementDownloadRepository $downloadFileRepository;
    #[Inject]
    protected AttachmentRepository $attachmentRepository;
    #[Inject]
    protected TenantNotificationQueueRepository $tenantNotificationQueueRepository;

    // 创建订单
    public function createOrder(array $data, string $source = ''): array
    {
        // 查询租户获取配置
        /** @var ModelTenant $findTenant */
        $findTenant = $this->tenantRepository->getQuery()->where('tenant_id', $data['tenant_id'])->first();
        if (!$findTenant || !$findTenant->is_payment) {
            throw new OpenApiException(ResultCode::ORDER_TENANT_NOT_OPEN_PAYMENT);
        }
        $request = Context::get(Request::class);
        $user = $request->user ?? null;
        $app = Context::get(ModelTenantApp::class);
        $tenantAccount = $this->tenantAccountRepository->getQuery()
            ->where('tenant_id', $data['tenant_id'])
            ->where('account_type', TenantAccount::ACCOUNT_TYPE_PAY)
            ->with('tenant')
            ->first();
        if (!$tenantAccount) {
            throw new BusinessException(ResultCode::TENANT_ACCOUNT_NOT_EXIST);
        }
        // 计算收款费率
        $calculate = [
            'fixed_fee'       => 0.00,
            'rate_fee'        => 0.00,
            'rate_fee_amount' => 0.00,
        ];
        $rate_fee = bcdiv($findTenant->payment_fee_rate, '100', 4);
        if (in_array(Tenant::PAYMENT_FEE_TYPE_FIXED, $findTenant->payment_fee_type, true)) {
            $calculate['fixed_fee'] = $findTenant->payment_fixed_fee;
        }
        if (in_array(Tenant::PAYMENT_FEE_TYPE_RATE, $findTenant->payment_fee_type, true)) {
            $calculate['rate_fee'] = $findTenant->payment_fee_rate;
            $calculate['rate_fee_amount'] = bcmul($data['amount'], $rate_fee, 4);
        }
        $calculate['total_fee'] = bcadd($calculate['fixed_fee'], $calculate['rate_fee_amount'], 4);
        Db::beginTransaction();
        try {
            $disbursementOrder = $this->repository->create([
                'tenant_id'           => $data['tenant_id'],
                'tenant_order_no'     => $data['tenant_order_no'],
                'amount'              => $data['amount'],
                'order_source'        => $source,
                'notify_remark'       => $data['notify_remark'] ?? '',
                'notify_url'          => $data['notify_url'] ?? '',
                'fixed_fee'           => $calculate['fixed_fee'],
                'rate_fee'            => $calculate['rate_fee'],
                'rate_fee_amount'     => $calculate['rate_fee_amount'],
                'total_fee'           => $calculate['total_fee'],
                'settlement_amount'   => bcadd($data['amount'], $calculate['total_fee'], 4),
                'expire_time'         => date('Y-m-d H:i:s', strtotime('+' . $findTenant->payment_expire_minutes . ' minutes')),
                'payment_type'        => $data['payment_type'],
                'payee_bank_name'     => $data['payee_bank_name'] ?? '',
                'payee_bank_code'     => $data['payee_bank_code'] ?? '',
                'payee_account_name'  => $data['payee_account_name'] ?? '',
                'payee_account_no'    => $data['payee_account_no'] ?? '',
                'payee_phone'         => $data['payee_phone'] ?? '',
                'payee_upi'           => $data['payee_upi'] ?? '',
                'app_id'              => $app->id ?? 0,
                'status'              => DisbursementOrder::STATUS_CREATING,
                'request_id'          => $request->requestId,
                'customer_created_by' => $user->id ?? 0,
            ]);
            if (!filled($disbursementOrder)) {
                throw new BusinessException(ResultCode::ORDER_CREATE_FAILED);
            }
            Event::dispatch('disbursement-order-status-records', [
                'order_id' => $disbursementOrder->id,
                'status'   => DisbursementOrder::STATUS_CREATING,
                'desc_cn'  => $source . ' 订单创建中',
                'desc_en'  => $source . ' Order is being created',
                'remark'   => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);
            // 扣款
            $modelTransactionRecord = $this->transactionRecordRepository->orderTransaction(
                $disbursementOrder->id,
                $disbursementOrder->platform_order_no,
                $tenantAccount,
                -$disbursementOrder->amount,
                -$disbursementOrder->total_fee
            );
            if (!$modelTransactionRecord) {
                throw new Exception('Failed to update the recharge record');
            }
            $this->repository->getModel()->where('id', $disbursementOrder->id)->update([
                'transaction_record_id' => $modelTransactionRecord->id,
            ]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            throw $e;
        }
        // 执行成功，添加队列
        // 交易队列，防止回滚
        Event::dispatch('app.transaction.created', $modelTransactionRecord);
        $this->repository->updateById($disbursementOrder->id, [
            'platform_transaction_no' => $modelTransactionRecord->transaction_no,
        ]);
        return [
            'platform_order_no' => $disbursementOrder->platform_order_no,
            'tenant_order_no'   => $disbursementOrder->tenant_order_no,
            'amount'            => $disbursementOrder->amount,
            'status'            => $disbursementOrder->status,
        ];
    }

    // 核销订单
    public function writeOff(int $disbursementOrderId, int $transactionVoucherId): bool
    {
        /** @var ModelDisbursementOrder $order */
        $order = $this->repository->getQuery()->find($disbursementOrderId);
        if (!$order) {
            throw new BusinessException(ResultCode::ORDER_NOT_FOUND);
        }
        if (!in_array($order->status, [
            DisbursementOrder::STATUS_WAIT_FILL,
            DisbursementOrder::STATUS_SUSPEND,
            DisbursementOrder::STATUS_INVALID
        ], true)) {
            throw new BusinessException(ResultCode::ORDER_STATUS_ERROR);
        }

        $transactionVoucher = $this->transactionVoucherRepository->findById($transactionVoucherId);
        if (!$transactionVoucher) {
            throw new BusinessException(ResultCode::TRANSACTION_VOUCHER_NOT_EXIST);
        }
        Db::beginTransaction();
        try {
            // 更新凭证表 collection_status order_no
            $isOk = $this->transactionVoucherRepository->writeOff($transactionVoucherId, $order->platform_order_no);
            if (!$isOk) {
                throw new Exception('The update of the voucher table failed');
            }
            // 更新订单表 transaction_voucher_id  status
            $isOk = $this->repository->getQuery()
                ->where('id', $disbursementOrderId)
                ->where(function (Builder $query) {
                    $query->where('status', DisbursementOrder::STATUS_WAIT_FILL)
                        ->orWhere('status', DisbursementOrder::STATUS_SUSPEND)
                        ->orWhere('status', DisbursementOrder::STATUS_INVALID);
                })
                ->update([
                    'status'                 => DisbursementOrder::STATUS_SUCCESS,
                    'transaction_voucher_id' => $transactionVoucherId,
                    'pay_time'               => date('Y-m-d H:i:s'),
                    'utr'                    => $transactionVoucher->transaction_voucher_type === TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UTR ?
                        $transactionVoucher->transaction_voucher : '',
                ]);
            if (!$isOk) {
                throw new Exception('Failed to update the order');
            }

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw new BusinessException(ResultCode::ORDER_VERIFY_FAILED, $exception->getMessage());
        }
        $collection_source = TransactionVoucher::getHumanizeValueDouble(TransactionVoucher::$collection_source_list, $transactionVoucher->collection_source);
        $voucher_type = TransactionVoucher::getHumanizeValueDouble(TransactionVoucher::$transaction_voucher_type_list, $transactionVoucher->transaction_voucher_type);
        Event::dispatch('disbursement-order-status-records', [
            'order_id' => $disbursementOrderId,
            'status'   => DisbursementOrder::STATUS_SUCCESS,
            'desc_cn'  => '订单支付成功, 核销凭证(类型：' . $voucher_type['zh'] . " 来源:{$collection_source['zh']}[ID:{$transactionVoucherId}]" . ')',
            'desc_en'  => 'Order has been paid, write off voucher(Type: ' . $voucher_type['en'] . " Source:{$collection_source['en']}" . '[ID:' . $transactionVoucherId . '])',
            'remark'   => $transactionVoucher->content,
        ]);
        // 回调通知队列
        $disbursementOrder = $this->repository->findById($disbursementOrderId);
        // 更新对应渠道账户 收款金额 和 收款次数 channelAccountRepository bankAccountRepository
        if ($disbursementOrder->channel_account_id > 0) {
            $this->channelAccountRepository->getQuery()
                ->where('id', $disbursementOrder->channel_account_id)
                ->update([
                    'today_payment_amount' => Db::raw('today_receipt_amount+' . $disbursementOrder->amount),
                    'today_payment_count'  => Db::raw('today_receipt_count+1'),
                ]);
        }
        if ($disbursementOrder->bank_account_id > 0) {
            $this->bankAccountRepository->getQuery()
                ->where('id', $disbursementOrder->bank_account_id)
                ->update([
                    'today_payment_amount' => Db::raw('today_receipt_amount+' . $disbursementOrder->amount),
                    'today_payment_count'  => Db::raw('today_receipt_count+1'),
                ]);
        }

        $this->notify($disbursementOrder, [
            'tenant_id'         => $disbursementOrder->tenant_id,
            'platform_order_no' => $disbursementOrder->platform_order_no,
            'tenant_order_no'   => $disbursementOrder->tenant_order_no,
            'status'            => $disbursementOrder->status,
            'pay_time'          => $disbursementOrder->pay_time,
            'refund_at'         => $disbursementOrder->refund_at,
            'refund_reason'     => $disbursementOrder->refund_reason,
            'amount'            => number_format($disbursementOrder->amount, 2, '.', ''),
            'total_fee'         => number_format($disbursementOrder->total_fee, 2, '.', ''),
            'settlement_amount' => number_format($disbursementOrder->settlement_amount, 2, '.', ''),
            'utr'               => $disbursementOrder->utr,
            'notify_remark'     => $disbursementOrder->notify_remark,
            'created_at'        => $disbursementOrder->created_at,
        ], 5);
        // 构建交易凭证图片并存储
        $disbursementOrder->payment_voucher_image = env('APP_DOMAIN') . $this->repository->buildOrderPaymentImage($disbursementOrder);
        $disbursementOrder->save();
        return $isOk;
    }

    // 管理员取消订单
    public function cancelById(mixed $id, int $operatorId, string $username, string $requestId): bool
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                return $this->cancelById($item, $operatorId, $username, $requestId);
            }
        }
        return Db::transaction(function () use ($id, $operatorId, $username, $requestId) {
            $cancelOkNum = false;
            // 如果 $id 是数字或字符串，则尝试将 $id 转换为数字
            if (is_numeric($id) || is_string($id)) {
                $disbursementOrder = $this->repository->getQuery()
                    ->with('channel:id,channel_name,channel_code')
                    ->where('id', $id)
                    ->whereIn('status', [
                        DisbursementOrder::STATUS_CREATED,
                        DisbursementOrder::STATUS_ALLOCATED,
                        DisbursementOrder::STATUS_SUSPEND,
                    ])
                    ->first();
                if (!$disbursementOrder) {
                    return false;
                }
                // 判断是上游订单，取消上游订单
                if ($disbursementOrder->channel_type == DisbursementOrder::CHANNEL_TYPE_UPSTREAM) {
                    $isCancelByUpstreamOrder = $this->cancelByUpstreamOrderId($disbursementOrder);
                    if (!$isCancelByUpstreamOrder) {
                        return false;
                    }
                }
                $cancelOkNum = $this->repository->getModel()
                    ->where('id', $id)
                    ->whereIn('status', [
                        DisbursementOrder::STATUS_CREATED,
                        DisbursementOrder::STATUS_ALLOCATED,
                        DisbursementOrder::STATUS_SUSPEND,
                    ])
                    ->update([
                        'status'       => DisbursementOrder::STATUS_CANCEL,
                        'cancelled_by' => $operatorId,
                        'cancelled_at' => date('Y-m-d H:i:s'),
                    ]);
                if (!$cancelOkNum) {
                    return false;
                }
                Event::dispatch('disbursement-order-status-records', [
                    'order_id' => $id,
                    'status'   => DisbursementOrder::STATUS_CANCEL,
                    'desc_cn'  => "平台管理员{$username}[" . $operatorId . '] 取消订单',
                    'desc_en'  => "Platform administrator {$username}[" . $operatorId . '] cancel order',
                    'remark'   => json_encode([
                        'request_id' => $requestId,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                Redis::send(DisbursementOrder::DISBURSEMENT_ORDER_REFUND_QUEUE_NAME, [
                    'ids'           => [$id],
                    'refund_reason' => 'Order canceled by platform administrator'
                ]);
            }
            return $cancelOkNum;
        });
    }

    // 取消上游订单
    private function cancelByUpstreamOrderId(ModelDisbursementOrder $disbursementOrder): bool
    {

        // 判断是上游订单，取消上游订单
        $channel_code = $disbursementOrder['channel']['channel_code'] ?? '';
        if (!filled($channel_code)) {
            return false;
        }
        $className = Tenant::$upstream_disbursement_options[$channel_code] ?? '';
        if (!filled($className)) {
            return false;
        }
        $channelAccount = $this->channelAccountRepository->findById($disbursementOrder->channel_account_id);
        if (!$channelAccount) {
            return false;
        }
        try {
            // 使用 TransactionDisbursementOrderFactory 调用上游接口
            $service = TransactionDisbursementOrderFactory::getInstance($className)->init($channelAccount);
            // 调用创建订单接口
            $cancelOk = $service->cancelOrder($disbursementOrder->platform_order_no, $disbursementOrder->upstream_order_no);
            if (!$cancelOk) {
                Log::error("cancelByCustomerId: 上游取消订单失败", [
                    'channel_code'      => $channel_code,
                    'merchant_id'       => $channelAccount->merchant_id,
                    'platform_order_no' => $disbursementOrder->platform_order_no,
                    'upstream_order_no' => $disbursementOrder->upstream_order_no,
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("cancelByCustomerId: 取消上游订单异常", [
                'channel_code'      => $channel_code,
                'merchant_id'       => $channelAccount->merchant_id,
                'platform_order_no' => $disbursementOrder->platform_order_no,
                'upstream_order_no' => $disbursementOrder->upstream_order_no,
                'message'           => $e->getMessage(),
                'trace'             => $e->getTraceAsString(),
            ]);
            return false;
        }
        return true;
    }

    // 客户取消订单
    public function cancelByCustomerId(mixed $id, string $tenantId, int $customerId, string $username, string $requestId): bool
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                return $this->cancelByCustomerId($item, $tenantId, $customerId, $username, $requestId);
            }
        }
        return Db::transaction(function () use ($id, $tenantId, $customerId, $username, $requestId) {
            $cancelOkNum = false;
            // 如果 $id 是数字或字符串，则尝试将 $id 转换为数字
            if (is_numeric($id) || is_string($id)) {
                $disbursementOrder = $this->repository->getQuery()
                    ->with('channel:id,channel_name,channel_code')
                    ->where('id', $id)
                    ->where('tenant_id', $tenantId)
                    ->whereIn('status', [
                        DisbursementOrder::STATUS_CREATED,
                        DisbursementOrder::STATUS_ALLOCATED,
                        DisbursementOrder::STATUS_SUSPEND,
                    ])
                    ->first();
                if (!$disbursementOrder) {
                    return false;
                }
                // 判断是上游订单，取消上游订单
                if ($disbursementOrder->channel_type == DisbursementOrder::CHANNEL_TYPE_UPSTREAM) {
                    $isCancelByUpstreamOrder = $this->cancelByUpstreamOrderId($disbursementOrder);
                    if (!$isCancelByUpstreamOrder) {
                        return false;
                    }
                }
                $cancelOkNum = $this->repository->getModel()
                    ->where('id', $id)
                    ->whereIn('status', [
                        DisbursementOrder::STATUS_CREATED,
                        DisbursementOrder::STATUS_ALLOCATED,
                        DisbursementOrder::STATUS_SUSPEND,
                    ])
                    ->update([
                        'status'                => DisbursementOrder::STATUS_CANCEL,
                        'customer_cancelled_by' => $customerId,
                        'cancelled_at'          => date('Y-m-d H:i:s'),
                    ]);
                if (!$cancelOkNum) {
                    return false;
                }
                Event::dispatch('disbursement-order-status-records', [
                    'order_id' => $id,
                    'status'   => DisbursementOrder::STATUS_CANCEL,
                    'desc_cn'  => "商户用户{$username}[" . $customerId . '] 取消订单',
                    'desc_en'  => "Merchant user {$username}[" . $customerId . '] cancel order',
                    'remark'   => json_encode([
                        'request_id' => $requestId,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                Redis::send(DisbursementOrder::DISBURSEMENT_ORDER_REFUND_QUEUE_NAME, [
                    'ids'           => [$id],
                    'refund_reason' => 'Order canceled by the customer'
                ]);
            }
            return $cancelOkNum;
        });
    }

    // 分配
    public function distribute(array $params, int $operatorId, string $username, string $requestId): bool
    {
        return Db::transaction(function () use ($params, $operatorId, $username, $requestId) {
            $updateId = $this->repository->getQuery()
                ->whereIn('id', $params['ids'])
                ->whereIn('status', [
                    DisbursementOrder::STATUS_CREATED,
                    DisbursementOrder::STATUS_SUSPEND,
                ])
                ->update([
                    'status'                  => DisbursementOrder::STATUS_ALLOCATED,
                    'disbursement_channel_id' => $params['disbursement_channel_id'],
                    'channel_type'            => $params['channel_type'],
                    'bank_account_id'         => $params['channel_type'] === DisbursementOrder::CHANNEL_TYPE_BANK ?
                        $params['bank_account_id'] : 0,
                    'channel_account_id'      => $params['channel_type'] === DisbursementOrder::CHANNEL_TYPE_UPSTREAM ?
                        $params['channel_account_id'] : 0,
                    'updated_at'              => date('Y-m-d H:i:s'),
                ]);
            if ($updateId) {
                $channel_type_id = $params['channel_type'] === DisbursementOrder::CHANNEL_TYPE_BANK ? $params['bank_account_id'] : $params['channel_account_id'];
                $channel_type_msg = DisbursementOrder::getHumanizeValueDouble(DisbursementOrder::$channel_type_list, $params['channel_type']);
                Event::dispatch('disbursement-order-status-records', [
                    'order_id' => $params['ids'],
                    'status'   => DisbursementOrder::STATUS_ALLOCATED,
                    'desc_cn'  => "平台管理员{$username}[" . $operatorId . '] 分配订单（类型:' . $channel_type_msg['zh'] . ' 渠道ID:' . $channel_type_id . '）',
                    'desc_en'  => "Platform administrator {$username}[" . $operatorId . '] allocate orders (Type:' . $channel_type_msg['en'] . ' Channel ID:' . $channel_type_id . '）',
                    'remark'   => json_encode([
                        'request_id'     => $requestId,
                        'request_params' => $params,
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                // 如果是上游渠道类型，添加到上游创建订单队列
                if ($params['channel_type'] === DisbursementOrder::CHANNEL_TYPE_UPSTREAM) {
                    $this->addToUpstreamCreateQueue($params['ids'], $params);
                }
            }
            return $updateId;
        });
    }

    // 自动分配
    public function autoDistribute(int $disbursement_order_id, int $channel_account_id): bool
    {
        // 查询 $channel_account_id
        /** @var ModelChannelAccount $account */
        $account = $this->channelAccountRepository->getQuery()
            ->with('channel')
            ->where('id', $channel_account_id)
            ->first();
        if (!$account) {
            return false;
        }
        return Db::transaction(function () use ($disbursement_order_id, $account, $channel_account_id) {
            $updateId = $this->repository->getQuery()
                ->where('id', $disbursement_order_id)
                ->where('status', '=', DisbursementOrder::STATUS_CREATED)
                ->update([
                    'status'                  => DisbursementOrder::STATUS_ALLOCATED,
                    'disbursement_channel_id' => $account->channel_id,
                    'channel_account_id'      => $channel_account_id,
                    'channel_type'            => DisbursementOrder::CHANNEL_TYPE_UPSTREAM,
                ]);
            if ($updateId) {
                Event::dispatch('disbursement-order-status-records', [
                    'order_id' => $disbursement_order_id,
                    'status'   => DisbursementOrder::STATUS_ALLOCATED,
                    'desc_cn'  => "系统自动分配订单到 上游渠道 {$account['channel']['channel_code']}（渠道账户ID:" . $account['merchant_id'] . '）',
                    'desc_en'  => "System automatically allocates orders to upstream channel {$account['channel']['channel_code']} (Channel Account ID:" . $account['merchant_id'] . ')',
                ]);
            }
            return $updateId;
        });
    }

    // 定时每分钟检测自动重新分配
    public function autoReallocateCrontab(): void
    {
        // 查询开启自动分配的租户，获取租户id, 查询订单
        $tenants = $this->tenantRepository->getQuery()
            ->where('auto_assign_enabled', '=', 1)
            ->pluck('tenant_id');
        if (!$tenants) {
            return;
        }
        var_dump('定时每分钟检测自动重新分配==', $tenants);
        $disbursementOrders = $this->repository->getQuery()
            ->whereIn('tenant_id', $tenants)
            ->where('status', DisbursementOrder::STATUS_CREATED)
            ->get();
        $disbursementOrders?->each(function ($disbursementOrder) {
            Event::dispatch('app.tenant.auto_assign', [
                'tenant_id' => $disbursementOrder->tenant_id,
                'order_id'  => $disbursementOrder->id,
            ]);
        });
    }

    public function downloadBankBill(array $params, int $operatorId, string $username, string $requestId): Response
    {
        $down_bill_template_id = $params['down_bill_template_id'] ?? 'icici';
        $ids = $params['ids'] ?? [];
        $bill_config = config('bankbill.' . $down_bill_template_id);
        if (!filled($bill_config)) {
            throw new BusinessException(ResultCode::ORDER_BANK_BILL_TEMPLATE_NOT_EXIST);
        }
        if (!filled($ids)) {
            throw new BusinessException(ResultCode::ORDER_NOT_FOUND);
        }
        $disbursementOrders = $this->repository->getQuery()
            ->whereIn('id', $ids)
            ->where('status', DisbursementOrder::STATUS_ALLOCATED)
            ->where('channel_type', DisbursementOrder::CHANNEL_TYPE_BANK)
            ->with('bank_account:id,branch_name,account_holder,account_number,bank_code')
            ->get();
        if (!$disbursementOrders) {
            throw new BusinessException(ResultCode::ORDER_NOT_FOUND);
        }
        try {
            $excelData = $bill_config['down_dto_class']::formatData($disbursementOrders);
        } catch (\Throwable $e) {
            throw new BusinessException(ResultCode::ORDER_BANK_BILL_TEMPLATE_RUNTIME_ERROR, $e->getMessage());
        }

        $down_filename = $bill_config['down_filename'] ?? 'order_' . date('YmdHis');
        // $down_filename 如果值带bank_card_no，替换bank_card_no为变量 account_number
        $down_filename = str_replace('bank_card_no', $disbursementOrders[0]['bank_account']['account_number'], $down_filename);
        $down_filepath = $bill_config['down_filepath'] ?? '/public/download/file/';
        $down_suffix = $bill_config['down_suffix'] ?? 'xlsx';
        $download_path = str_replace('/public', '', $down_filepath);
        $hash = md5(json_encode($excelData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $filename = $down_filename . '.' . $down_suffix;

        /** @var ModelBankDisbursementDownload $filesInfo */
        if ($filesInfo = $this->downloadFileRepository->getModel()->where(['hash' => $hash])->first()) {
            return (new Response(200, [
                'Server'                        => env('APP_NAME', 'LangDaLang'),
                'access-control-expose-headers' => 'content-disposition',
            ]))->download(BASE_PATH . $filesInfo->path, $filename)
                ->header('Content-Disposition', "attachment; filename={$filename}; filename*=UTF-8''" . rawurlencode($filename));
        }
        $result = (new PhpOffice($bill_config['down_dto_class']))->export($down_filename, $down_suffix, $down_filepath, $excelData, null, $bill_config['down_sheetIndex'] ?? 0);
        // 将文件大小转换为MB（注意：1MB = 1048576字节）
        $address = BASE_PATH . $down_filepath . $down_filename . '.' . $down_suffix;
        $fileSizeBytes = filesize($address);
        $fileSizeMB = formatSize($fileSizeBytes);

        // 检查文件是否已存在
        $attachment = $this->downloadFileRepository->getModel()->where(['hash' => $hash])->first();
        if (!$attachment) {
            $inData = [
                'storage_mode' => 'local',
                'origin_name'  => $down_filename,
                'object_name'  => $down_filename,
                'hash'         => $hash,
                'mime_type'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'storage_path' => $address,
                'base_path'    => $download_path,
                'suffix'       => 'xlsx',
                'size_byte'    => $fileSizeBytes,
                'size_info'    => formatBytes($fileSizeBytes),
                'url'          => env('APP_DOMAIN', 'http://127.0.0.1:9501') . $download_path . $down_filename . '.xlsx',
            ];
            $attachment = $this->attachmentRepository->getModel()->create($inData);
        }

        $downloadData = [
            'file_name'     => $down_filename,
            'attachment_id' => $attachment->id,
            'path'          => $down_filepath . $down_filename . '.' . $down_suffix,
            'hash'          => $hash,
            'file_size'     => $fileSizeMB,
            'record_count'  => count($excelData),
            'created_by'    => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'suffix'        => $down_suffix,
        ];
        $downloadFile = $this->downloadFileRepository->create($downloadData);

        // 更新订单状态
        $isUpdate = $this->repository->getModel()->whereIn('id', $ids)
            ->where('status', DisbursementOrder::STATUS_ALLOCATED)
            ->update([
                'status'                        => DisbursementOrder::STATUS_WAIT_FILL,
                'down_bill_template_id'         => $down_bill_template_id,
                'bank_disbursement_download_id' => $downloadFile->id,
            ]);
        if ($isUpdate) {
            Event::dispatch('disbursement-order-status-records', [
                'order_id' => $ids,
                'status'   => DisbursementOrder::STATUS_WAIT_FILL,
                'desc_cn'  => "平台管理员{$username}[" . $operatorId . '] 导出订单（模版ID:' . $down_bill_template_id . ' 文件哈希:' . $hash . '）',
                'desc_en'  => "Platform administrator {$username}[" . $operatorId . '] export orders (Template ID:' . $down_bill_template_id . ' File Hash:' . $hash . '）',
                'remark'   => json_encode([
                    'request_id'     => $requestId,
                    'request_params' => $params,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }

        return $result;

    }

    // 退款
    public function refund(int $orderId, string $refund_reason = ''): bool
    {
        $disbursementOrder = $this->repository->findById($orderId);
        if (!$disbursementOrder) {
            return false;
        }
        $tenantAccount = $this->tenantAccountRepository->getQuery()
            ->where('tenant_id', $disbursementOrder->tenant_id)
            ->where('account_type', TenantAccount::ACCOUNT_TYPE_PAY)
            ->with('tenant')
            ->first();
        if (!$tenantAccount) {
            throw new BusinessException(ResultCode::TENANT_ACCOUNT_NOT_EXIST);
        }
        $refund_at = date('Y-m-d H:i:s');
        Db::beginTransaction();
        try {
            $updateOk = $this->repository->getModel()
                ->where('id', $orderId)
                ->whereNull('refund_at')
                ->update([
                    'refund_reason' => $refund_reason,
                    'refund_at'     => $refund_at,
                ]);
            if (!$updateOk) {
                throw new RuntimeException('The order status does not meet the refund conditions, the current status value is:' . $disbursementOrder->status);
            }

            $modelTransactionRecord = $this->transactionRecordRepository->orderTransaction(
                $orderId,
                $disbursementOrder->platform_order_no,
                $tenantAccount,
                $disbursementOrder->amount,
                $disbursementOrder->total_fee,
                1,0,'',
                TransactionRecord::TYPE_ORDER_REFUND
            );

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw $e;
        }
        // 交易队列，防止回滚
        Event::dispatch('app.transaction.created', $modelTransactionRecord);
        // 回调通知队列
        $disbursementOrderNotify = $this->repository->findById($orderId);
        $this->notify($disbursementOrderNotify, [
            'tenant_id'         => $disbursementOrderNotify->tenant_id,
            'platform_order_no' => $disbursementOrderNotify->platform_order_no,
            'tenant_order_no'   => $disbursementOrderNotify->tenant_order_no,
            'status'            => $disbursementOrderNotify->status,
            'pay_time'          => $disbursementOrder->pay_time,
            'refund_at'         => $refund_at,
            'refund_reason'     => $disbursementOrderNotify->refund_reason,
            'amount'            => number_format($disbursementOrderNotify->amount, 2, '.', ''),
            'total_fee'         => number_format($disbursementOrderNotify->total_fee, 2, '.', ''),
            'settlement_amount' => number_format($disbursementOrderNotify->settlement_amount, 2, '.', ''),
            'utr'               => $disbursementOrderNotify->utr,
            'notify_remark'     => $disbursementOrderNotify->notify_remark,
            'created_at'        => $disbursementOrderNotify->created_at,
        ], 5);
        // 构建交易凭证图片并存储
        $disbursementOrder->payment_voucher_image = env('APP_DOMAIN') . $this->repository->buildOrderPaymentImage($disbursementOrder);
        $disbursementOrder->platform_transaction_no = $modelTransactionRecord->transaction_no;
        $disbursementOrder->save();
        return true;
    }

    // 回调通知
    public function notify(ModelDisbursementOrder $disbursementOrder, array $data, int $max_retry_count = 1): bool
    {
        if (!$disbursementOrder || !filled($disbursementOrder->notify_url)) {
            return false;
        }
        $insertOk = $this->tenantNotificationQueueRepository->create([
            'tenant_id'             => $disbursementOrder->tenant_id,
            'app_id'                => $disbursementOrder->app_id,
            'account_type'          => TenantAccount::ACCOUNT_TYPE_PAY,
            'disbursement_order_id' => $disbursementOrder->id,
            'notification_type'     => TenantNotificationQueue::NOTIFICATION_TYPE_ORDER,
            'notification_url'      => $disbursementOrder->notify_url,
            'max_retry_count'       => $max_retry_count,
            'request_data'          => json_encode($data, JSON_THROW_ON_ERROR)
        ]);
        if (!$insertOk) {
            return false;
        }
        $tenantNotificationQueue = $this->tenantNotificationQueueRepository->findById($insertOk->id);
        if ($tenantNotificationQueue->execute_status === TenantNotificationQueue::EXECUTE_STATUS_WAITING && filled($tenantNotificationQueue->notification_url)) {
            var_dump('待执行回调通知队列 TenantNotificationQueue');
            \Webman\RedisQueue\Redis::send(TenantNotificationQueue::TENANT_NOTIFICATION_QUEUE_NAME, [
                'id'                    => $tenantNotificationQueue->id,
                'tenant_id'             => $tenantNotificationQueue->tenant_id,
                'app_id'                => $tenantNotificationQueue->app_id,
                'account_type'          => $tenantNotificationQueue->account_type,
                'disbursement_order_id' => $tenantNotificationQueue->disbursement_order_id,
                'notification_type'     => $tenantNotificationQueue->notification_type,
                'notification_url'      => $tenantNotificationQueue->notification_url,
                'request_method'        => $tenantNotificationQueue->request_method,
                'request_data'          => $tenantNotificationQueue->request_data,
                'max_retry_count'       => $tenantNotificationQueue->max_retry_count,
            ]);
        }
        return $this->repository->updateById($disbursementOrder->id, [
            'notify_status' => DisbursementOrder::NOTIFY_STATUS_CALLBACK_ING,
        ]);
    }

    // 冲正 Adjusted to payment failure
    public function adjustToFailure(int $orderId, string $source = '', string $param_remark = ''): bool
    {
        $disbursementOrder = $this->repository->findById($orderId);
        if (!$disbursementOrder) {
            return false;
        }
        if ($disbursementOrder->status !== DisbursementOrder::STATUS_SUCCESS) {
            return false;
        }
        $updateOk = $this->repository->getModel()
            ->where([
                'id'     => $orderId,
                'status' => DisbursementOrder::STATUS_SUCCESS,
            ])
            ->update([
                'status' => DisbursementOrder::AdjustToFailure,
            ]);
        if (!$updateOk) {
            return false;
        }
        Event::dispatch('disbursement-order-status-records', [
            'order_id' => $orderId,
            'status'   => DisbursementOrder::AdjustToFailure,
            'desc_cn'  => $source . ' 调整为付款失败',
            'desc_en'  => $source . ' adjusted to payment failure',
            'remark'   => $param_remark,
        ]);
        return $this->refund($orderId, 'Payment failure');
    }


    // 人工回调通知
    public function manualNotify(int $disbursementOrderId): bool
    {
        $disbursementOrder = $this->repository->findById($disbursementOrderId);
        if (!$disbursementOrder) {
            return false;
        }
        return $this->notify($disbursementOrder, [
            'tenant_id'         => $disbursementOrder->tenant_id,
            'platform_order_no' => $disbursementOrder->platform_order_no,
            'tenant_order_no'   => $disbursementOrder->tenant_order_no,
            'status'            => $disbursementOrder->status,
            'pay_time'          => $disbursementOrder->pay_time,
            'refund_at'         => $disbursementOrder->refund_at,
            'refund_reason'     => $disbursementOrder->refund_reason,
            'amount'            => number_format($disbursementOrder->amount, 2, '.', ''),
            'total_fee'         => number_format($disbursementOrder->total_fee, 2, '.', ''),
            'settlement_amount' => number_format($disbursementOrder->settlement_amount, 2, '.', ''),
            'utr'               => $disbursementOrder->utr,
            'notify_remark'     => $disbursementOrder->notify_remark,
            'created_at'        => $disbursementOrder->created_at,
        ]);
    }

    public function statisticsSuccessfulOrderRateOfTelegramBot(string $tenant_id): array
    {
        $queryWhereSql = " and tenant_id = {$tenant_id}";
        $date = date('Y-m-d');
        // 10分钟内统计
        $order_num = $this->repository->queryCountOrderNum($queryWhereSql, $date, date('Y-m-d H:i:s'));
        $order_successful_num = $this->repository->queryOrderSuccessfulNum($queryWhereSql, $date, date('Y-m-d H:i:s'));
        // 10分钟内统计
        $order_num_10_minutes = $this->repository->queryCountOrderNum($queryWhereSql, date('Y-m-d H:i:s', strtotime('-10 minutes')), date('Y-m-d H:i:s'));
        $order_successful_num_10_minutes = $this->repository->queryOrderSuccessfulNum($queryWhereSql, date('Y-m-d H:i:s', strtotime('-10 minutes')), date('Y-m-d H:i:s'));
        // 30分钟内统计
        $order_num_30_minutes = $this->repository->queryCountOrderNum($queryWhereSql, date('Y-m-d H:i:s', strtotime('-30 minutes')), date('Y-m-d H:i:s'));
        $order_successful_num_30_minutes = $this->repository->queryOrderSuccessfulNum($queryWhereSql, date('Y-m-d H:i:s', strtotime('-30 minutes')), date('Y-m-d H:i:s'));
        // 60分钟内统计
        $order_num_60_minutes = $this->repository->queryCountOrderNum($queryWhereSql, date('Y-m-d H:i:s', strtotime('-60 minutes')), date('Y-m-d H:i:s'));
        $order_successful_num_60_minutes = $this->repository->queryOrderSuccessfulNum($queryWhereSql, date('Y-m-d H:i:s', strtotime('-60 minutes')), date('Y-m-d H:i:s'));

        return [
            'order_num'                          => $order_num,
            'order_successful_num'               => $order_successful_num,
            'payment_successful_rate'            => ($order_num > 0) ?
                (bcdiv((string)$order_successful_num, (string)$order_num, 4) * 100) : 0,
            // 10分钟内统计
            'order_num_10_minutes'               => $order_num_10_minutes,
            'order_successful_num_10_minutes'    => $order_successful_num_10_minutes,
            'payment_successful_rate_10_minutes' => $order_num_10_minutes > 0 ?
                (bcdiv((string)$order_successful_num_10_minutes, (string)$order_num_10_minutes, 4) * 100) : 0.00,
            // 30分钟内统计
            'order_num_30_minutes'               => $order_num_30_minutes,
            'order_successful_num_30_minutes'    => $order_successful_num_30_minutes,
            'payment_successful_rate_30_minutes' => $order_num_30_minutes > 0 ?
                (bcdiv((string)$order_successful_num_30_minutes, (string)$order_num_30_minutes, 4) * 100) : 0.00,
            // 60分钟内统计
            'order_num_60_minutes'               => $order_num_60_minutes,
            'order_successful_num_60_minutes'    => $order_successful_num_60_minutes,
            'payment_successful_rate_60_minutes' => $order_num_60_minutes > 0 ?
                (bcdiv((string)$order_successful_num_60_minutes, (string)$order_num_60_minutes, 4) * 100) : 0.00,
        ];
    }

    // 分析统计最近一周的订单
    #[Cacheable(
        prefix: 'disbursement:collection:order:number:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function statisticsOrderNumberOfWeek(int $userId): array
    {
        // 计算近7天的日期范围，每天的订单数量
        $parallel = new Parallel(7);
        $user = Context::get('user');
        for ($i = 6; $i >= 0; $i--) {
            $parallel->add(function () use ($i, $user) {
                Context::set('user', $user);
                $query = $this->repository->getQuery();
                $date = date('Y-m-d', strtotime('-' . $i . ' day'));
                $date_range[$date] = $this->repository->getModel()->scopeWithTenantPermission($query)->where('created_at', '>=', $date)->where('created_at', '<', date('Y-m-d', strtotime('+1 day', strtotime($date))))->count();
                return $date_range;
            });
        }
        $results = $parallel->wait();
        // order_num_range 合并 $results 的值
        $order_num_range = array_merge(...$results);
        // $order_num_range 数组排序
        ksort($order_num_range);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime('-6 days'));
        return [
            'count'     => $order_num_range[$today],
            'yesterday' => $order_num_range[$yesterday],
            'growth'    => (int)bcsub($order_num_range[$today], $order_num_range[$yesterday], 0),
            'chartData' => format_chart_data_x_y_date_count($order_num_range, $startDate, $endDate),
        ];
    }

    #[Cacheable(
        prefix: 'statistics:disbursement:order:successful:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function statisticsOrderSuccessfulNumberOfWeek(int $userId): array
    {
        // 计算近7天的日期范围，每天的成功订单数量
        $parallel = new Parallel(7);
        $user = Context::get('user');
        for ($i = 6; $i >= 0; $i--) {
            $parallel->add(function () use ($i, $user) {
                Context::set('user', $user);
                $query = $this->repository->getQuery();
                $date = date('Y-m-d', strtotime('-' . $i . ' day'));
                $date_range[$date] = $this->repository->getModel()->scopeWithTenantPermission($query)
                    ->where('status', DisbursementOrder::STATUS_SUCCESS)
                    ->where('pay_time', '>=', $date)
                    ->where('pay_time', '<', date('Y-m-d', strtotime('+1 day', strtotime($date))))
                    ->count();
                return $date_range;
            });
        }
        $results = $parallel->wait();
        // order_num_range 合并 $results 的值
        $order_num_range = array_merge(...$results);
        // $order_num_range 数组排序
        ksort($order_num_range);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime('-6 days'));
        return [
            'count'     => $order_num_range[$today],
            'yesterday' => $order_num_range[$yesterday],
            'growth'    => (int)bcsub($order_num_range[$today], $order_num_range[$yesterday], 0),
            'chartData' => format_chart_data_x_y_date_count($order_num_range, $startDate, $endDate),
        ];
    }

    #[Cacheable(
        prefix: 'statistics:disbursement:order:amount:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function statisticsOrderSuccessfulAmountOfWeek(int $userId): array
    {
        // 计算近7天的日期范围，每天的成功订单数量
        $parallel = new Parallel(7);
        $user = Context::get('user');
        for ($i = 6; $i >= 0; $i--) {
            $parallel->add(function () use ($i, $user) {
                Context::set('user', $user);
                $query = $this->repository->getQuery();
                $date = date('Y-m-d', strtotime('-' . $i . ' day'));
                $total = $this->repository->getModel()->scopeWithTenantPermission($query)
                    ->where('status', DisbursementOrder::STATUS_SUCCESS)
                    ->where('pay_time', '>=', $date)
                    ->where('pay_time', '<', date('Y-m-d', strtotime('+1 day', strtotime($date))))
                    ->sum('amount');
                $date_range[$date] = number_format($total, 2, '.', ',');
                return $date_range;
            });
        }
        $results = $parallel->wait();
        // order_num_range 合并 $results 的值
        $order_num_range = array_merge(...$results);
        // $order_num_range 数组排序
        ksort($order_num_range);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime('-6 days'));
        return [
            'count'     => $order_num_range[$today],
            'yesterday' => $order_num_range[$yesterday],
            'growth'    => bcsub($order_num_range[$today], $order_num_range[$yesterday], 0),
            'chartData' => format_chart_data_x_y_date_count($order_num_range, $startDate, $endDate, '₹'),
        ];
    }


    #[Cacheable(
        prefix: 'statistics:disbursement-success-order:hour-today:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function getSuccessOrderCountByHourToday(int $userId): array
    {
        return $this->getSuccessOrderCountByHour($userId, date('Y-m-d'), date('Y-m-d'));
    }


    #[Cacheable(
        prefix: 'statistics:disbursement-success-order:hour-yesterday:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function getSuccessOrderCountByHourYesterday(int $userId): array
    {
        return $this->getSuccessOrderCountByHour($userId, date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')));
    }

    #[Cacheable(
        prefix: 'statistics:disbursement-success-order:hour-week:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function getSuccessOrderCountByHourWeek(int $userId): array
    {
        return $this->getSuccessOrderCountByHour($userId, date('Y-m-d', strtotime('-7 day')), date('Y-m-d'));
    }

    public function getSuccessOrderCountByHour(int $user_id, string $startDate, string $endDate): array
    {
        $query = $this->repository->getQuery();
        // 按小时分组获取今天的成功支付订单数量
        $order_num_range = $this->repository->getModel()
            ->scopeWithTenantPermission($query)
            ->select([
                'pay_time_hour',
                DB::raw('COUNT(*) as order_count')
            ])
            ->whereNotNull('pay_time')
            ->where('status', DisbursementOrder::STATUS_SUCCESS)
            ->where('pay_time_hour', '>=', date('Ymd', strtotime($startDate)) . '00')  // 今日0点开始
            ->where('pay_time_hour', '<=', date('Ymd', strtotime($endDate)) . '23')  // 今日23点结束
            ->groupBy('pay_time_hour')
            ->orderBy('pay_time_hour')
            ->get();

        return $order_num_range->toArray();
    }


    public function tenantGetSuccessOrderCountByHour(string $tenantId, string $startDate, string $endDate): array
    {
        $query = $this->repository->getQuery();
        // 按小时分组获取今天的成功支付订单数量
        $order_num_range = $this->repository->getQuery()
            ->select([
                'pay_time_hour',
                DB::raw('COUNT(*) as order_count')
            ])
            ->where('tenant_id', $tenantId)
            ->whereNotNull('pay_time')
            ->where('status', DisbursementOrder::STATUS_SUCCESS)
            ->where('pay_time_hour', '>=', date('Ymd', strtotime($startDate)) . '00')  // 今日0点开始
            ->where('pay_time_hour', '<=', date('Ymd', strtotime($endDate)) . '23')  // 今日23点结束
            ->groupBy('pay_time_hour')
            ->orderBy('pay_time_hour')
            ->get();

        return $order_num_range->toArray();
    }

    /**
     * 添加订单到上游创建队列
     * @param array $orderIds 订单ID数组
     * @param array $params 分配参数
     * @return void
     */
    public function addToUpstreamCreateQueue(array $orderIds, array $params = []): void
    {
        try {
            // 获取订单详情
            $orders = $this->repository->getQuery()
                ->whereIn('id', $orderIds)
                ->where('status', DisbursementOrder::STATUS_ALLOCATED)
                ->where('channel_type', DisbursementOrder::CHANNEL_TYPE_UPSTREAM)
                ->get();

            foreach ($orders as $order) {
                // 创建队列记录
                $queueItem = $this->upstreamCreateQueueRepository->create([
                    'platform_order_no'     => $order->platform_order_no,
                    'disbursement_order_id' => $order->id,
                    'tenant_id'             => $order->tenant_id,
                    'app_id'                => $order->app_id,
                    'channel_account_id'    => $order->channel_account_id,
                    'amount'                => $order->amount,
                    'payee_bank_name'       => $order->payee_bank_name,
                    'payee_bank_code'       => $order->payee_bank_code,
                    'payee_account_name'    => $order->payee_account_name,
                    'payee_account_no'      => $order->payee_account_no,
                    'payee_phone'           => $order->payee_phone,
                    'payee_upi'             => $order->payee_upi,
                    'payment_type'          => $order->payment_type,
                    'order_data'            => json_encode($order->toArray(), JSON_UNESCAPED_UNICODE),
                    'process_status'        => DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_WAIT,
                    'retry_count'           => 0,
                    'max_retry_count'       => DisbursementOrderUpstreamCreateQueue::DEFAULT_MAX_RETRY_COUNT,
                    'created_at'            => date('Y-m-d H:i:s'),
                    'updated_at'            => date('Y-m-d H:i:s'),
                ]);
                var_dump('addToUpstreamCreateQueue queueItem:====', $queueItem);
                Log::info("addToUpstreamCreateQueue queueItem: " . json_encode($queueItem, JSON_UNESCAPED_UNICODE));
                if ($queueItem) {
                    // 发送到队列消费者
                    Redis::send(DisbursementOrderUpstreamCreateQueue::CONSUMER_QUEUE_NAME, [
                        'queue_id' => $queueItem->id,
                    ]);

                    var_dump('发送到队列消费者==', $queueItem->id);
                }
            }
            return;
        } catch (Exception $e) {
            // 记录错误日志
            error_log("addToUpstreamCreateQueue error: " . $e->getMessage());
            return;
        }
    }
}
