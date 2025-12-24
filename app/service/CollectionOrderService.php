<?php

namespace app\service;

use app\constants\CollectionOrder;
use app\constants\Tenant;
use app\constants\TenantAccount;
use app\constants\TenantNotificationQueue;
use app\constants\TransactionVoucher;
use app\exception\BusinessException;
use app\exception\OpenApiException;
use app\lib\annotation\Cacheable;
use app\lib\enum\ResultCode;
use app\model\ModelCollectionOrder;
use app\model\ModelTenant;
use app\model\ModelTenantApp;
use app\repository\BankAccountRepository;
use app\repository\ChannelAccountRepository;
use app\repository\CollectionOrderRepository;
use app\repository\CollectionOrderStatusRecordsRepository;
use app\repository\TenantAccountRepository;
use app\repository\TenantNotificationQueueRepository;
use app\repository\TenantRepository;
use app\repository\TransactionRecordRepository;
use app\repository\TransactionVoucherRepository;
use app\tools\Base62Converter;
use app\upstream\Handle\TransactionCollectionOrderFactory;
use Carbon\Carbon;
use DI\Attribute\Inject;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use support\Context;
use support\Db;
use support\Log;
use support\Redis;
use Webman\Event\Event;
use Webman\Http\Request;
use Workerman\Coroutine\Parallel;

class CollectionOrderService extends BaseService
{
    #[Inject]
    public CollectionOrderRepository $repository;
    // CollectionOrderStatusRecordsRepository
    #[Inject]
    protected CollectionOrderStatusRecordsRepository $collectionOrderStatusRecordsRepository;
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
    protected TransactionRecordRepository $transactionRecordRepository;
    #[Inject]
    protected TenantNotificationQueueRepository $tenantNotificationQueueRepository;

    // 创建订单
    public function createOrder(array $data, string $source = ''): array
    {
        // 查询租户获取配置 当前租户没有开启收款功能
        $findTenant = $this->tenantRepository->getQuery()
            ->where('tenant_id', $data['tenant_id'])->first();
        if (!$findTenant || !$findTenant->is_receipt) {
            throw new OpenApiException(ResultCode::ORDER_TENANT_NOT_OPEN_RECEIPT);
        }
        // 没有可用的收款方式
        if (!filled($findTenant->collection_use_method)) {
            throw new OpenApiException(ResultCode::ORDER_NO_AVAILABLE_COLLECTION_METHOD);
        }
        // 启用上游第三方收款
        $upstream_enabled = $findTenant->upstream_enabled;

        $result = [];
        $fail_result = '';
        foreach ($findTenant->collection_use_method as $method) {
            if ($method === Tenant::COLLECTION_USE_METHOD_UPSTREAM && $upstream_enabled && filled($findTenant->upstream_items)) {
                var_dump('上游第三方收款');
                // 上游第三方收款
                try {
                    $result = $this->upstreamCollection($data, $findTenant, $source);
                    var_dump('上游第三方收款结果：', $result);
                } catch (\Throwable $e) {
                    $fail_result = $e->getMessage();
                    Log::warning('upstream_collection_error: ' . $e->getMessage());
                    continue;
                }
                if (filled($result)) {
                    break;
                }

            }
            if ($method === Tenant::COLLECTION_USE_METHOD_BANK_ACCOUNT) {
                // 银行收款
                try {
                    $result = $this->bankCollection($data, $findTenant, $source);
                } catch (\Throwable $e) {
                    $fail_result = $e->getMessage();
                    Log::warning('bank_collection_error: ' . $e->getMessage());
                    continue;
                }
                if (filled($result)) {
                    break;
                }
            }
        }
        if (filled($fail_result) && filled($result) === false) {
            throw new BusinessException(ResultCode::ORDER_CREATE_FAILED, $fail_result);
        }
        return $result;
    }

    // 银行收款
    public function bankCollection(array $data, ModelTenant $findTenant, string $source = ''): mixed
    {
        // 查询验证是否具有安全等级匹配的银行卡
        $checkBankCard = $this->bankAccountRepository->checkBankCardOfCollection($findTenant->safe_level, (float)$data['amount']);
        if (!$checkBankCard) {
            throw new BusinessException(ResultCode::ORDER_NO_MATCHING_BANK_CARD);
        }
        // 获取等级匹配的银行卡
        $bankCardQuery = $this->bankAccountRepository->getBankCardOfCollectionQuery($findTenant->safe_level, (float)$data['amount']);
        $card = match ($findTenant->card_acquire_type) {
            Tenant::BANK_CARD_SEQUENTIAL => $bankCardQuery->orderBy('id', 'desc')->first(),
            Tenant::BANK_CARD_POLLING => $bankCardQuery->orderBy('last_used_time', 'asc')->first(),
            default => $bankCardQuery->inRandomOrder()->first(),
        };
        if (!$card) {
            throw new BusinessException(ResultCode::ORDER_NO_MATCHING_BANK_CARD);
        }
        $card->last_used_time = date('Y-m-d H:i:s');
        $card->save();

        // 计算收款费率
        $calculate = [
            'fixed_fee'       => 0.00,
            'rate_fee'        => 0.00,
            'rate_fee_amount' => 0.00,
        ];
        $rate_fee = bcdiv($findTenant->receipt_fee_rate, '100', 4);
        if (in_array(Tenant::RECEIPT_FEE_TYPE_FIXED, $findTenant->receipt_fee_type, true)) {
            $calculate['fixed_fee'] = $findTenant->receipt_fixed_fee;
        }
        if (in_array(Tenant::RECEIPT_FEE_TYPE_RATE, $findTenant->receipt_fee_type, true)) {
            $calculate['rate_fee'] = $findTenant->receipt_fee_rate;
            $calculate['rate_fee_amount'] = bcmul($data['amount'], $rate_fee, 4);
        }
        $calculate['total_fee'] = bcadd($calculate['fixed_fee'], $calculate['rate_fee_amount'], 4);

        $payable_amount = $data['amount'];
        if ($findTenant->float_enabled) {
            if (count($findTenant->float_range) !== 2 || bccomp((string)$findTenant->float_range[0], (string)$findTenant->float_range[1], 2) !== -1) {
                throw new BusinessException(ResultCode::ORDER_COLLECTION_FLOAT_AMOUNT_ERROR);
            }
            [
                $min,
                $max
            ] = $findTenant->float_range;
            if (bcadd((string)$data['amount'], (string)$min) <= 0) {
                throw new BusinessException(ResultCode::ORDER_COLLECTION_AMOUNT_LESS_THAN_MIN_FLOAT_AMOUNT);
            }
            try {
                $floatAmount = $this->getFloatAmount($card->id, $data['amount'], $findTenant);
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                if ($errorMsg === 'There is not enough available floating amount') {
                    $errorMsg .= ": bank account " . $card->account_number;
                }
                throw new BusinessException(ResultCode::ORDER_COLLECTION_FLOAT_AMOUNT_ERROR, $errorMsg);
            }
            $payable_amount = $floatAmount;
        }
        $request = Context::get(Request::class);
        $user = $request->user ?? null;
        $app = Context::get(ModelTenantApp::class);
        $cashier_template = Tenant::$cashier_template_list[$findTenant->cashier_template] ?? 'DEFAULT';
        // 收款订单创建
        $collectionOrder = $this->repository->create([
            'tenant_id'             => $data['tenant_id'],
            'tenant_order_no'       => $data['tenant_order_no'],
            'amount'                => $data['amount'],
            'payable_amount'        => $payable_amount,
            'fixed_fee'             => $calculate['fixed_fee'],
            'rate_fee'              => $calculate['rate_fee'],
            'rate_fee_amount'       => $calculate['rate_fee_amount'],
            'total_fee'             => $calculate['total_fee'],
            'settlement_amount'     => bcsub($data['amount'], $calculate['total_fee'], 4),
            'settlement_type'       => $findTenant->receipt_settlement_type,
            //            'settlement_type'       => CollectionOrder::SETTLEMENT_TYPE_NOT_SETTLED,
            'collection_type'       => CollectionOrder::COLLECTION_TYPE_BANK_ACCOUNT,
            'collection_channel_id' => $card->channel_id,
            'bank_account_id'       => $card->id,
            'expire_time'           => date('Y-m-d H:i:s', strtotime('+' . $findTenant->receipt_expire_minutes . ' minutes')),
            'order_source'          => $source,
            'notify_remark'         => $data['notify_remark'] ?? '',
            'return_url'            => $data['return_url'] ?? '',
            'notify_url'            => $data['notify_url'] ?? '',
            'app_id'                => $app->id ?? 0,
            'payer_name'            => $card->account_holder,
            'payer_account'         => $card->account_number,
            'payer_bank'            => $card->branch_name,
            'payer_ifsc'            => $card->bank_code,
            'payer_upi'             => $card->upi_id,
            'status'                => CollectionOrder::STATUS_PROCESSING,
            'request_id'            => $request->requestId,
            'customer_created_by'   => $user->id ?? 0,
            'settlement_delay_mode' => $findTenant->settlement_delay_mode,
            'settlement_delay_days' => $findTenant->settlement_delay_days,
        ]);
        $collectionOrder->pay_url = config('app.cash_desk_url') . "/payment/{$cashier_template}/" . $collectionOrder->platform_order_no;
        $collectionOrder->save();
        if (!filled($collectionOrder)) {
            throw new BusinessException(ResultCode::ORDER_CREATE_FAILED);
        }
        Event::dispatch('collection-order-status-records', [
            'order_id' => $collectionOrder->id,
            'status'   => CollectionOrder::STATUS_PROCESSING,
            'desc_cn'  => $source . '[' . $card->account_number . '] 订单创建成功,支付中...',
            'desc_en'  => $source . '[' . $card->account_number . '] The order was created successfully, and the payment was underway...',
            'remark'   => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);
        return $this->formatCreatOrderResult($collectionOrder, $cashier_template);

    }

    /**
     * 获取充值千分位小数点浮动金额
     * @throws Exception
     */
    public function getFloatAmount(int $bank_card_id, float $amount, ModelTenant $tenant): float
    {
        [
            $min,
            $max
        ] = $tenant->float_range;
        $min100 = floor($min * 100);
        $max100 = floor($max * 100);
        $amount100 = $amount * 100;
        $range_diff = $max100 - $min100;
        $max_attempts = max(1, (int)$range_diff);
        $cache_key = CollectionOrder::ORDER_FLOAT_AMOUNT_CACHE_KEY . 'cid_' . $bank_card_id . "_amount_{$amount100}";
        $keysRedis = Redis::keys($cache_key . '_*');
        $countKeysRedis = count($keysRedis);
        if ($range_diff <= $countKeysRedis - 2) {
            throw new Exception('There is not enough available floating amount');
        }
        try {
            $random = random_int((int)$min100 + 1, (int)$max100 - 1);
        } catch (\RangeException|\Exception $e) {
            $random = mt_rand((int)$min100 + 1, (int)$max100 - 1);
        }
        $floatAmount = $random / 100;
        if ($floatAmount === 0) {
            return $this->getFloatAmount($bank_card_id, $amount, $tenant);
        }
        $resultAmount = $amount + $floatAmount;
        // 如果结果小于0.01，则重新生成随机数  判断最终返回结果不能是负数
        if ($resultAmount < 0.01) {
            return $this->getFloatAmount($bank_card_id, $amount, $tenant);
        }
        $resultAmount100 = $resultAmount * 100;
        // 查询redis
        $redisValue = Redis::get($cache_key . '_' . $resultAmount100);
        if ($redisValue) {
            return $this->getFloatAmount($bank_card_id, $amount, $tenant);
        }
        // 查数据库 collection_order 待支付状态 的 payable_amount = $resultAmount 是否存在
        if ($this->repository->getQuery()
            ->where('bank_account_id', $bank_card_id)
            ->where('payable_amount', $resultAmount)
            ->where('status', CollectionOrder::STATUS_PROCESSING)
            ->exists()
        ) {
            return $this->getFloatAmount($bank_card_id, $amount, $tenant);
        }

        $exTime = abs((int)$tenant->receipt_expire_minutes + (int)$tenant->reconcile_retain_minutes) * 60;
        $ok = Redis::setex($cache_key . '_' . $resultAmount100, $exTime, 1);
        if (!$ok) {
            throw new Exception('Failed to set redis');
        }
        return $resultAmount;
    }


    public function formatCreatOrderResult(ModelCollectionOrder $collectionOrder, string $cashier_template = 'DEFAULT'): array
    {
        $platform_order_no = $collectionOrder->platform_order_no;
        $tenant_order_no = $collectionOrder->tenant_order_no;
        $pay_url = $collectionOrder->pay_url ?? config('app.cash_desk_url') . "/payment/{$cashier_template}/" . $collectionOrder->platform_order_no;

        $order_id_code = Base62Converter::decToBase62($collectionOrder->id, 5);

        if (filled($collectionOrder->payer_upi)) {
            $upi = $collectionOrder->payer_upi;
            $upi_str = explode('@', $collectionOrder->payer_upi);
            $sign = "MEYCIQCHBg/RU0nnqGczGT+3qmufIH0d4syWKuN/93J8Of+pVwIhAJRHuz0ouV+LC1+MLU9is5mIfphzIYAnLb9yRKM7lXA+";
            return [
                'platform_order_no' => $platform_order_no,
                'tenant_order_no'   => $tenant_order_no,
                'amount'            => number_format($collectionOrder->amount, 2, '.', ''),
                'payable_amount'    => number_format($collectionOrder->payable_amount, 2, '.', ''),
                'status'            => $collectionOrder->status,
                'payer_upi'         => $collectionOrder->payer_upi,
                'pay_time'          => $collectionOrder->pay_time,
                'expire_time'       => $collectionOrder->expire_time,
                'return_url'        => $collectionOrder->return_url,
                'created_at'        => $collectionOrder->created_at,
                'pay_url'           => $pay_url,
                'meta'              => [
                    'paytm'   => "paytmmp://cash_wallet?pa={$upi}&pn={$upi_str[0]}&tr={$collectionOrder->platform_order_no}&tn={$order_id_code}&am={$collectionOrder->payable_amount}&cu=INR&mc=5641&url=&mode=02&purpose=00&orgid=159002&sign={$sign}&featuretype=money_transfer",
                    'upi'     => "upi://pay?pa={$upi}&pn=Payment To {$upi_str[0]}&am={$collectionOrder->payable_amount}&tn={$order_id_code}&cu=INR&tr={$collectionOrder->platform_order_no}",
                    'gpay'    => "gpay://pay?pa={$upi}&pn={$upi_str[0]}&tr={$collectionOrder->platform_order_no}&am={$collectionOrder->payable_amount}&tn={$order_id_code}&cu=INR&mc=5641",
                    'phonepe' => "phonepe://pay?pa={$upi}&pn={$upi_str[0]}&tr={$collectionOrder->platform_order_no}&tn={$order_id_code}&am={$collectionOrder->payable_amount}&cu=INR&mc=5641&url=&mode=02&purpose=00&orgid=159002&sign={$sign}",

                ],
            ];
        }
        return [
            'platform_order_no' => $platform_order_no,
            'tenant_order_no'   => $tenant_order_no,
            'amount'            => $collectionOrder->amount,
            'payable_amount'    => $collectionOrder->payable_amount,
            'status'            => $collectionOrder->status,
            'payer_upi'         => $collectionOrder->payer_upi,
            'pay_time'          => $collectionOrder->pay_time,
            'expire_time'       => $collectionOrder->expire_time,
            'return_url'        => $collectionOrder->return_url,
            'created_at'        => $collectionOrder->created_at,
            'pay_url'           => $pay_url,
            'meta'              => [
                'paytm'   => '',
                'upi'     => '',
                'gpay'    => '',
                'phonepe' => '',
            ],

        ];
    }

    // createOrderOfUpstream
    public function upstreamCollection(array $data, ModelTenant $findTenant, string $source = ''): array
    {
        $createOrderResult = [];
        $fail_message = '';
        $channel_account = null;
        $success_created_service = '';
        if (!filled($findTenant->upstream_items)) {
            $fail_message = 'No upstream items';
            throw new \RuntimeException($fail_message);
        }
        foreach ($findTenant->upstream_items as $channelAccountId) {
            // 查询 渠道状态 且 满足限额
            $channel_account = $this->channelAccountRepository
                ->getChannelAccountOfCollectionQuery($channelAccountId, $data['amount'])
                ->first();
            if ($channel_account && isset($channel_account['channel']['channel_code'])) {
                $className = Tenant::$upstream_options[$channel_account['channel']['channel_code']] ?? '';
                var_dump('$className ', $className);
                if (filled($className)) {
                    // 错误前缀
                    $error_prefix = $channel_account['channel']['channel_code'] . ' createOrder fail:';
                    try {
                        $service = TransactionCollectionOrderFactory::getInstance($className)->init($channel_account);
                        $createOrderResult = $service->createOrder($data['tenant_order_no'], $data['amount']);
                    } catch (\Throwable $e) {
                        $fail_message = $error_prefix . $e->getMessage();
                        Log::warning($className . ' 创建订单失败' . $e->getMessage());
                        continue;
                    }
                    if (filled($createOrderResult) && isset($createOrderResult['ok'])) {
                        //   [
                        //        'ok'     => 'bool',
                        //        'msg'    => 'string',
                        //        'origin' => 'string',
                        //        'data'   => [
                        //            '_upstream_order_no' => 'string',
                        //            '_order_amount'      => 'string',
                        //            '_pay_upi'           => 'string',
                        //            '_pay_url'           => 'string',
                        //            '_utr'               => 'string'
                        // ]
                        if ($createOrderResult['ok'] === true) {
                            $success_created_service = $channel_account['channel']['channel_code'];
                            break;
                        }
                        $fail_message = $error_prefix . ($createOrderResult['msg'] ?? 'nil');
                        Log::warning($fail_message);
                        continue;
                    }
                }
            }
        }
        if (!$channel_account || filled($fail_message) || filled($createOrderResult) === false) {
            throw new \RuntimeException($fail_message);
        }
        // 计算收款费率
        $calculate = [
            'fixed_fee'       => 0.00,
            'rate_fee'        => 0.00,
            'rate_fee_amount' => 0.00,
        ];
        $rate_fee = bcdiv($findTenant->receipt_fee_rate, '100', 4);
        if (in_array(Tenant::RECEIPT_FEE_TYPE_FIXED, $findTenant->receipt_fee_type, true)) {
            $calculate['fixed_fee'] = $findTenant->receipt_fixed_fee;
        }
        if (in_array(Tenant::RECEIPT_FEE_TYPE_RATE, $findTenant->receipt_fee_type, true)) {
            $calculate['rate_fee'] = $findTenant->receipt_fee_rate;
            $calculate['rate_fee_amount'] = bcmul($data['amount'], $rate_fee, 4);
        }
        $calculate['total_fee'] = bcadd($calculate['fixed_fee'], $calculate['rate_fee_amount'], 4);
        $request = Context::get(Request::class);
        $collectionOrder = $this->repository->create([
            'tenant_id'             => $data['tenant_id'],
            'tenant_order_no'       => $data['tenant_order_no'],
            'upstream_order_no'     => $createOrderResult['data']['_upstream_order_no'] ?? '',
            'amount'                => $data['amount'],
            'payable_amount'        => $createOrderResult['data']['_order_amount'] ?? $data['amount'],
            'fixed_fee'             => $calculate['fixed_fee'],
            'rate_fee'              => $calculate['rate_fee'],
            'rate_fee_amount'       => $calculate['rate_fee_amount'],
            'total_fee'             => $calculate['total_fee'],
            'settlement_amount'     => bcsub($data['amount'], $calculate['total_fee'], 4),
            'settlement_type'       => $findTenant->receipt_settlement_type,
            //            'settlement_type'       => CollectionOrder::SETTLEMENT_TYPE_NOT_SETTLED,
            'collection_type'       => CollectionOrder::COLLECTION_TYPE_UPSTREAM,
            'collection_channel_id' => $channel_account->channel_id,
            'channel_account_id'    => $channel_account->id,
            'expire_time'           => date('Y-m-d H:i:s', strtotime('+' . $findTenant->receipt_expire_minutes . ' minutes')),
            'order_source'          => $source,
            'notify_remark'         => $data['notify_remark'] ?? '',
            'return_url'            => $data['return_url'] ?? '',
            'notify_url'            => $data['notify_url'] ?? '',
            'app_id'                => $app->id ?? 0,
            'payer_upi'             => $createOrderResult['data']['_pay_upi'] ?? '',
            'status'                => CollectionOrder::STATUS_PROCESSING,
            'request_id'            => $request->requestId,
            'customer_created_by'   => $user->id ?? 0,
            'settlement_delay_mode' => $findTenant->settlement_delay_mode,
            'settlement_delay_days' => $findTenant->settlement_delay_days,
            'pay_url'               => $createOrderResult['data']['_pay_url'] ?? '',
        ]);
        Event::dispatch('collection-order-status-records', [
            'order_id'   => $collectionOrder->id,
            'status'     => CollectionOrder::STATUS_PROCESSING,
            'desc_cn'    => $source . '[' . $success_created_service . '] 订单创建成功,支付中...',
            'desc_en'    => $source . '[' . $success_created_service . '] The order was created successfully, and the payment was underway...',
            'created_at' => date('Y-m-d H:i:s'),
            'remark'     => $createOrderResult['origin'],
        ]);
        return $this->formatCreatOrderResult($collectionOrder);
    }

    // 定时任务监听订单失效
    public function orderExpire(): void
    {
        $needUpdate = $this->repository->getQuery()
            ->where('status', CollectionOrder::STATUS_PROCESSING)
            ->where('expire_time', '<', Carbon::now())
            ->get();
        foreach ($needUpdate as $item) {
            $isUpdate = $this->repository->updateById($item->id, [
                'status' => CollectionOrder::STATUS_INVALID,
            ]);
            if ($isUpdate) {
                Event::dispatch('collection-order-status-records', [
                    'order_id' => $item->id,
                    'status'   => CollectionOrder::STATUS_INVALID,
                    'desc_cn'  => '订单已失效',
                    'desc_en'  => 'Order has expired',
                    'remark'   => json_encode([
                        'created_at'  => $item->created_at,
                        'expire_time' => $item->expire_time,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    // 核销
    public function writeOff(int $collectionOrderId, int $transactionVoucherId): bool
    {
        /** @var ModelCollectionOrder $order */
        $order = $this->repository->getQuery()->find($collectionOrderId, [
            'id',
            'status',
            'platform_order_no',
            'tenant_id',
            'settlement_type',
            'amount',
            'paid_amount',
            'rate_fee',
            'fixed_fee',
            'settlement_delay_mode',
            'settlement_delay_days',
        ]);
        if (!$order) {
            throw new BusinessException(ResultCode::ORDER_NOT_FOUND);
        }
        if (!in_array($order->status, [
            CollectionOrder::STATUS_PROCESSING,
            CollectionOrder::STATUS_SUSPEND,
            CollectionOrder::STATUS_INVALID
        ], true)) {
            throw new BusinessException(ResultCode::ORDER_STATUS_ERROR);
        }
        $tenantAccount = $this->tenantAccountRepository->getQuery()
            ->where('tenant_id', $order->tenant_id)
            ->where('account_type', TenantAccount::ACCOUNT_TYPE_RECEIVE)
            ->with('tenant')
            ->first();
        if (!$tenantAccount) {
            throw new BusinessException(ResultCode::TENANT_ACCOUNT_NOT_EXIST);
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
                throw new \RuntimeException('The update of the voucher table failed');
            }
            // 加帐
            $settlement_type = $order->settlement_type;
            $settlement_amount = $transactionVoucher->amount;
            if ($settlement_type === CollectionOrder::SETTLEMENT_TYPE_PAID_AMOUNT) {
                $settlement_amount = $transactionVoucher->collection_amount;
            }
            $rate_fee_amount = bcdiv(bcmul((string)$settlement_amount, (string)$order->rate_fee, 4), '100', 4);
            $fee_amount = bcadd($rate_fee_amount, $order->fixed_fee);
            $modelTransactionRecord = $this->transactionRecordRepository->orderTransaction(
                $order->id,
                $order->platform_order_no,
                $tenantAccount,
                $settlement_amount,
                -$fee_amount,
                $order->settlement_delay_mode,
                $order->settlement_delay_days,
            );
            if (!$modelTransactionRecord) {
                throw new Exception('Failed to update the recharge record');
            }
            dump('更新订单表 transaction_voucher_id  status isOk');
            // 更新订单表 transaction_voucher_id  status
            $isOk = $this->repository->getQuery()
                ->where('id', $collectionOrderId)
                ->where(function (Builder $query) {
                    $query->where('status', CollectionOrder::STATUS_PROCESSING)
                        ->orWhere('status', CollectionOrder::STATUS_SUSPEND)
                        ->orWhere('status', CollectionOrder::STATUS_INVALID);
                })
                ->update([
                    'status'                  => CollectionOrder::STATUS_SUCCESS,
                    'transaction_voucher_id'  => $transactionVoucherId,
                    'settlement_type'         => $settlement_type,
                    'rate_fee_amount'         => $rate_fee_amount,
                    'total_fee'               => $fee_amount,
                    'platform_transaction_no' => $modelTransactionRecord->transaction_no,
                    'paid_amount'             => $transactionVoucher->collection_amount,
                    'settlement_amount'       => bcsub((string)$settlement_amount, (string)$fee_amount, 4),
                    'pay_time'                => date('Y-m-d H:i:s'),
                    'utr'                     => $transactionVoucher->transaction_voucher_type === TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UTR ?
                        $transactionVoucher->transaction_voucher : '',
                ]);
            if (!$isOk) {
                throw new \RuntimeException('Failed to update the order');
            }
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw new BusinessException(ResultCode::ORDER_VERIFY_FAILED, $exception->getMessage());
        }
        // 执行成功，添加队列
        // 交易队列, 防止回滚
        Event::dispatch('app.transaction.created', $modelTransactionRecord);
        Event::dispatch('collection-order-status-records', [
            'order_id' => $collectionOrderId,
            'status'   => CollectionOrder::STATUS_SUCCESS,
            'desc_cn'  => '订单支付成功',
            'desc_en'  => 'Order has been paid',
            'remark'   => $transactionVoucher->content,
        ]);
        dump('回调通知队列=======');
        // 回调通知队列
        $collectionOrder = $this->repository->findById($collectionOrderId);
        // 更新对应渠道账户 收款金额 和 收款次数 channelAccountRepository bankAccountRepository
        if ($collectionOrder->channel_account_id > 0) {
            $this->channelAccountRepository->getQuery()
                ->where('id', $collectionOrder->channel_account_id)
                ->update([
                    'used_quota'           => Db::raw('used_quota+' . $collectionOrder->paid_amount),
                    'today_receipt_amount' => Db::raw('today_receipt_amount+' . $collectionOrder->paid_amount),
                    'today_receipt_count'  => Db::raw('today_receipt_count+1'),
                ]);
        }
        if ($collectionOrder->bank_account_id > 0) {
            $this->bankAccountRepository->getQuery()
                ->where('id', $collectionOrder->bank_account_id)
                ->update([
                    'used_quota'           => Db::raw('used_quota+' . $collectionOrder->paid_amount),
                    'today_receipt_amount' => Db::raw('today_receipt_amount+' . $collectionOrder->paid_amount),
                    'today_receipt_count'  => Db::raw('today_receipt_count+1'),
                ]);
        }

        $this->notify($collectionOrder, [
            'tenant_id'         => $collectionOrder->tenant_id,
            'platform_order_no' => $collectionOrder->platform_order_no,
            'tenant_order_no'   => $collectionOrder->tenant_order_no,
            'status'            => $collectionOrder->status,
            'pay_time'          => $collectionOrder->pay_time,
            'amount'            => number_format($collectionOrder->amount, 2, '.', ''),
            'total_fee'         => number_format($collectionOrder->total_fee, 2, '.', ''),
            'settlement_amount' => number_format($collectionOrder->settlement_amount, 2, '.', ''),
            'utr'               => $collectionOrder->utr,
            'notify_remark'     => $collectionOrder->notify_remark,
            'created_at'        => $collectionOrder->created_at,
        ], 5);
        $collectionOrder->platform_transaction_no = $modelTransactionRecord->transaction_no;
        $collectionOrder->save();
        return $isOk;
    }

    public function cancelById(mixed $id, int $operatorId, string $username, string $requestId): int
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                return $this->cancelById($item, $operatorId, $username, $requestId);
            }
        }
        return Db::transaction(function () use ($id, $operatorId, $username, $requestId) {
            $updateId = $this->repository->getModel()
                ->where('id', $id)
                ->where('status', '<=', CollectionOrder::STATUS_PROCESSING)
                ->update([
                    'status'       => CollectionOrder::STATUS_CANCEL,
                    'cancelled_by' => $operatorId,
                    'cancelled_at' => date('Y-m-d H:i:s'),
                ]);
            if ($updateId) {
                // 记录状态
                Event::dispatch('collection-order-status-records', [
                    'order_id' => $id,
                    'status'   => CollectionOrder::STATUS_CANCEL,
                    'desc_cn'  => "平台管理员{$username}[" . $operatorId . '] 取消订单',
                    'desc_en'  => "Platform administrator {$username}[" . $operatorId . '] cancel order',
                    'remark'   => json_encode([
                        'request_id' => $requestId,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            }
            return $updateId;
        });
    }

    public function cancelByCustomerId(mixed $id, string $tenantId, int $customerId, string $username, string $requestId): int
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                return $this->cancelByCustomerId($item, $tenantId, $customerId, $username, $requestId);
            }
        }
        return Db::transaction(function () use ($id, $tenantId, $customerId, $username, $requestId) {
            $updateId = $this->repository->getModel()
                ->where('id', $id)
                ->where('status', '<=', CollectionOrder::STATUS_PROCESSING)
                ->update([
                    'status'                => CollectionOrder::STATUS_CANCEL,
                    'customer_cancelled_by' => $customerId,
                    'cancelled_at'          => date('Y-m-d H:i:s'),
                ]);
            if ($updateId) {
                // 记录状态
                Event::dispatch('collection-order-status-records', [
                    'order_id' => $id,
                    'status'   => CollectionOrder::STATUS_CANCEL,
                    'desc_cn'  => "商户用户{$username}[" . $customerId . '] 取消订单',
                    'desc_en'  => "Merchant user {$username}[" . $customerId . '] cancel order',
                    'remark'   => json_encode([
                        'request_id' => $requestId,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            }
            return $updateId;
        });
    }

    // 回调通知
    public function notify(ModelCollectionOrder $collectionOrder, array $data, int $max_retry_count = 1): bool
    {
        if (!filled($collectionOrder->notify_url)) {
            return false;
        }
        $insertOk = $this->tenantNotificationQueueRepository->create([
            'tenant_id'           => $collectionOrder->tenant_id,
            'app_id'              => $collectionOrder->app_id,
            'account_type'        => TenantAccount::ACCOUNT_TYPE_RECEIVE,
            'collection_order_id' => $collectionOrder->id,
            'notification_type'   => TenantNotificationQueue::NOTIFICATION_TYPE_ORDER,
            'notification_url'    => $collectionOrder->notify_url,
            'max_retry_count'     => $max_retry_count,
            'request_data'        => json_encode($data, JSON_THROW_ON_ERROR)
        ]);
        if (!$insertOk) {
            return false;
        }
        $tenantNotificationQueue = $this->tenantNotificationQueueRepository->findById($insertOk->id);
        dump('待执行回调通知队列 TenantNotificationQueue', $insertOk);
        if ($tenantNotificationQueue->execute_status === TenantNotificationQueue::EXECUTE_STATUS_WAITING && filled($tenantNotificationQueue->notification_url)) {
            var_dump('待执行回调通知队列 TenantNotificationQueue-===========');
            \Webman\RedisQueue\Redis::send(TenantNotificationQueue::TENANT_NOTIFICATION_QUEUE_NAME, [
                'id'                  => $tenantNotificationQueue->id,
                'tenant_id'           => $tenantNotificationQueue->tenant_id,
                'app_id'              => $tenantNotificationQueue->app_id,
                'account_type'        => $tenantNotificationQueue->account_type,
                'collection_order_id' => $tenantNotificationQueue->collection_order_id,
                'notification_type'   => $tenantNotificationQueue->notification_type,
                'notification_url'    => $tenantNotificationQueue->notification_url,
                'request_method'      => $tenantNotificationQueue->request_method,
                'request_data'        => $tenantNotificationQueue->request_data,
                'max_retry_count'     => $tenantNotificationQueue->max_retry_count,
            ]);
        }

        return $this->repository->updateById($collectionOrder->id, [
            'notify_status' => CollectionOrder::NOTIFY_STATUS_CALLBACK_ING,
        ]);
    }

    // 人工回调通知
    public function manualNotify(int $collectionOrderId): bool
    {
        $collectionOrder = $this->repository->findById($collectionOrderId);
        if (!$collectionOrder) {
            return false;
        }
        return $this->notify($collectionOrder, [
            'tenant_id'         => $collectionOrder->tenant_id,
            'platform_order_no' => $collectionOrder->platform_order_no,
            'tenant_order_no'   => $collectionOrder->tenant_order_no,
            'status'            => $collectionOrder->status,
            'pay_time'          => $collectionOrder->pay_time,
            'amount'            => number_format($collectionOrder->amount, 2, '.', ''),
            'total_fee'         => number_format($collectionOrder->total_fee, 2, '.', ''),
            'settlement_amount' => number_format($collectionOrder->settlement_amount, 2, '.', ''),
            'utr'               => $collectionOrder->utr,
            'notify_remark'     => $collectionOrder->notify_remark,
            'created_at'        => $collectionOrder->created_at,
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
        prefix: 'statistics:collection:order:number:userId',
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
        prefix: 'statistics:collection:order:successful:userId',
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
                    ->where('status', CollectionOrder::STATUS_SUCCESS)
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
        prefix: 'statistics:collection:order:amount:userId',
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
                    ->where('status', CollectionOrder::STATUS_SUCCESS)
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

    // getSuccessOrderCountByHourToday
    #[Cacheable(
        prefix: 'statistics:collection-success-order:hour-today:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function getSuccessOrderCountByHourToday(int $userId): array
    {
        return $this->getSuccessOrderCountByHour($userId, date('Y-m-d'), date('Y-m-d'));
    }

    #[Cacheable(
        prefix: 'statistics:collection-success-order:hour-yesterday:userId',
        value: '_#{userId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function getSuccessOrderCountByHourYesterday(int $userId): array
    {
        return $this->getSuccessOrderCountByHour($userId, date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')));
    }

    #[Cacheable(
        prefix: 'statistics:collection-success-order:hour-week:userId',
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
            ->where('status', CollectionOrder::STATUS_SUCCESS)
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
            ->where('status', CollectionOrder::STATUS_SUCCESS)
            ->where('pay_time_hour', '>=', date('Ymd', strtotime($startDate)) . '00')  // 今日0点开始
            ->where('pay_time_hour', '<=', date('Ymd', strtotime($endDate)) . '23')  // 今日23点结束
            ->groupBy('pay_time_hour')
            ->orderBy('pay_time_hour')
            ->get();

        return $order_num_range->toArray();
    }

}
