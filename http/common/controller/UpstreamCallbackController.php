<?php

namespace http\common\controller;

use app\constants\CollectionOrder;
use app\constants\DisbursementOrder;
use app\constants\Tenant;
use app\constants\TransactionVoucher;
use app\controller\BasicController;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\Middleware;
use app\router\Annotations\RequestMapping;
use app\router\Annotations\RestController;
use app\service\TransactionVoucherService;
use app\upstream\Handle\TransactionCollectionOrderFactory;
use app\upstream\Handle\TransactionDisbursementOrderFactory;
use DI\Attribute\Inject;
use http\common\middleware\ChannelCallbackRecordMiddleware;
use support\Request;
use support\Response;
use Webman\RateLimiter\Annotation\RateLimiter;
use Webman\RedisQueue\Redis;
use Webman\Route;

#[RestController("/callback")]
class UpstreamCallbackController extends BasicController
{

    #[Inject]
    protected TransactionVoucherService $transactionVoucherService;

    #[RequestMapping(path: '/collection/{channel_code}/{channel_account_id}', methods: 'GET,POST'), RateLimiter(limit: 10)]
    #[Middleware(ChannelCallbackRecordMiddleware::class)]
    public function collection_order(Request $request, string $channel_code, int $channel_account_id): Response
    {
        return $this->handleCallback($request, $channel_code, $channel_account_id, 'collection');
    }

    // 打款订单回调通知
    #[RequestMapping(path: '/disbursement/{channel_code}/{channel_account_id}', methods: 'GET,POST'), RateLimiter(limit: 10)]
    #[Middleware(ChannelCallbackRecordMiddleware::class)]
    public function disbursement_order(Request $request, string $channel_code, int $channel_account_id): Response
    {
        return $this->handleCallback($request, $channel_code, $channel_account_id, 'disbursement');
    }

    #[GetMapping('/route')]
    public function routers(Request $request): Response
    {
        $routers = Route::getRoutes();
        var_dump($routers);
        return $this->success($routers);
    }

    /**
     * 统一处理回调逻辑
     */
    private function handleCallback(Request $request, string $channel_code, int $channel_account_id, string $callbackType): Response
    {
        // 获取服务类名
        $className = $this->getServiceClassName($channel_code, $callbackType);
        if (!$className) {
            return $this->error(ResultCode::INVALID_CHANNEL);
        }

        // 获取服务实例并处理回调
        $service = $this->getServiceInstance($className);
        $result = $service->notify($request);
        if (!$result) {
            return $this->error(ResultCode::FAIL);
        }

        // 处理回调结果
        return $this->processCallbackResult($result, $channel_account_id, $callbackType, $service);
    }

    /**
     * 获取服务类名
     */
    private function getServiceClassName(string $channelCode, string $callbackType): ?string
    {
        $options = match ($callbackType) {
            'collection' => Tenant::$upstream_options,
            'disbursement' => Tenant::$upstream_disbursement_options,
            default => []
        };

        $className = $options[$channelCode] ?? '';
        return $className && class_exists($className) ? $className : null;
    }

    /**
     * 获取服务实例
     */
    private function getServiceInstance(string $className): object
    {
        // 检查是否为收款服务
        if (in_array($className, Tenant::$upstream_options)) {
            return TransactionCollectionOrderFactory::getInstance($className);
        }

        // 检查是否为打款服务
        if (in_array($className, Tenant::$upstream_disbursement_options)) {
            return TransactionDisbursementOrderFactory::getInstance($className);
        }

        throw new \InvalidArgumentException("Unsupported service class: {$className}");
    }

    /**
     * 处理回调结果
     */
    private function processCallbackResult(array $result, int $channelAccountId, string $callbackType, object $service): Response
    {
        if (!($result['ok'] ?? false)) {
            return $service->notifyReturn(false);
        }

        // 提取交易凭证信息
        $voucherInfo = $this->extractTransactionVoucher($result['data'] ?? []);
        if (!$voucherInfo) {
            return $service->notifyReturn(false);
        }

        // 检查是否已存在相同的交易凭证
        if ($this->isTransactionVoucherExists($voucherInfo['type'], $voucherInfo['voucher'])) {
            return $service->notifyReturn(true);
        }

        // 根据回调类型处理业务逻辑
        $success = match ($callbackType) {
            'collection' => $this->handleCollectionCallback($result, $channelAccountId),
            'disbursement' => $this->handleDisbursementCallback($result),
            default => false
        };

        return $service->notifyReturn($success);
    }

    /**
     * 提取交易凭证信息
     */
    private function extractTransactionVoucher(array $data): ?array
    {
        if (isset($data['_utr']) && filled($data['_utr'])) {
            return [
                'type'    => TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UTR,
                'voucher' => $data['_utr']
            ];
        }

        if (isset($data['_upstream_order_no']) && filled($data['_upstream_order_no'])) {
            return [
                'type'    => TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UPSTREAM_ORDER_NO,
                'voucher' => $data['_upstream_order_no']
            ];
        }

        if (isset($data['_platform_order_no']) && filled($data['_platform_order_no'])) {
            return [
                'type'    => TransactionVoucher::TRANSACTION_VOUCHER_TYPE_PLATFORM_ORDER_NO,
                'voucher' => $data['_platform_order_no']
            ];
        }

        return null;
    }

    /**
     * 检查交易凭证是否已存在
     */
    private function isTransactionVoucherExists(string $type, string $voucher): bool
    {
        return $this->transactionVoucherService->repository->getQuery()
            ->where([
                'transaction_voucher_type' => $type,
                'transaction_voucher'      => $voucher,
            ])->exists();
    }

    /**
     * 处理收款回调
     */
    private function handleCollectionCallback(array $result, int $channelAccountId): bool
    {
        // 从请求中获取通道信息 (由中间件设置)
        $request = request();
        $callbackRecord = $request->callback_record ?? null;
        if (!$callbackRecord || !isset($callbackRecord->channel_id)) {
            return false;
        }

        $voucherInfo = $this->extractTransactionVoucher($result['data'] ?? []);

        $tv = $this->transactionVoucherService->repository->create([
            'channel_id'               => $callbackRecord->channel_id,
            'channel_account_id'       => $channelAccountId,
            'collection_amount'        => $result['data']['_amount'] ?? 0,
            'collection_time'          => $result['data']['_pay_time'] ?? date('Y-m-d H:i:s'),
            'collection_status'        => TransactionVoucher::COLLECTION_STATUS_WAITING,
            'collection_source'        => TransactionVoucher::COLLECTION_SOURCE_INTERNAL,
            'transaction_voucher_type' => $voucherInfo['type'],
            'transaction_voucher'      => $voucherInfo['voucher'],
            'content'                  => $result['origin'] ?? '',
            'transaction_type'         => TransactionVoucher::TRANSACTION_TYPE_COLLECTION
        ]);
        if (!$tv) {
            return false;
        }

        return Redis::send(CollectionOrder::COLLECTION_ORDER_WRITE_OFF_QUEUE_NAME, [
            'transaction_voucher_id'   => $tv->id,
            'transaction_voucher_type' => $tv->transaction_voucher_type,
            'transaction_voucher'      => $tv->transaction_voucher,
            'channel_id'               => $tv->channel_id,
            'bank_account_id'          => $tv->bank_account_id,
        ]);
    }

    /**
     * 处理打款回调
     */
    private function handleDisbursementCallback(array $result): bool
    {
        return Redis::send(DisbursementOrder::DISBURSEMENT_ORDER_WRITE_OFF_QUEUE_NAME, [
            'upstream_order_no' => $result['data']['_upstream_order_no'] ?? '',
            'platform_order_no' => $result['data']['_platform_order_no'] ?? '',
            'amount'            => $result['data']['_amount'] ?? '0.00',
            'utr'               => $result['data']['_utr'] ?? '',
            'rejection_reason'  => $result['data']['_rejection_reason'] ?? '',
            'payment_status'    => $result['data']['_payment_status'],
            'order_data'        => $result['origin'] ?? '',
        ]);
    }
}