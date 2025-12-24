<?php

namespace app\upstream\aipay;

use app\model\enums\DisbursementOrderVerificationQueuePayStatus;
use app\model\ModelChannelAccount;
use app\model\ModelDisbursementOrder;
use app\upstream\Handle\TransactionDisbursementOrderInterface;
use JetBrains\PhpStorm\ArrayShape;
use support\Request;
use support\Response;

class DisbursementService  extends Base implements TransactionDisbursementOrderInterface
{
    public function init(ModelChannelAccount $channel_account): TransactionDisbursementOrderInterface
    {
        $this->channel_account = $channel_account;
        $api_config_array = $this->channel_account->api_config;
        if (!filled($api_config_array)) {
            throw new \RuntimeException('Interface parameters not configured');
        }
        foreach ($api_config_array as $item) {
            if ($item['label'] === 'secret_key') {
                $this->secret_key = $item['value'];
            } elseif ($item['label'] === 'url') {
                $this->url = $item['value'];
            } elseif ($item['label'] === 'merchant_id') {
                $this->merchant_id = (int)$item['value'];
            } elseif ($item['label'] === 'return_url') {
                $this->return_url = $item['value'];
            }
        }
        return $this;
    }
    #[ArrayShape([
        'ok'     => 'bool',
        'msg'    => 'string',
        'origin' => 'string',
        'data'   => [
            '_upstream_order_no' => 'string',
        ]
    ])]
    public function createOrder(ModelDisbursementOrder $orderModel): array
    {
        var_dump('createOrder= aipay==',$orderModel->toArray());
        // TODO: Implement createOrder() method.
        throw new \Exception('sdtashadsdfgs===');
        $randOrderNo = 'ee' . time() . rand(1000, 9999);
        return [
            'ok'     => true,
            'msg'    => 'ok',
            'origin' => '{"code":"11","message":"test error","data":{"order_id":"ee33333"}}',
            'data'   => [
                '_upstream_order_no' => $randOrderNo,
            ]
        ];
    }

    public function queryOrder(string $platform_order_no, string $upstream_order_no): array
    {
        // TODO: Implement queryOrder() method.
    }

    public function cancelOrder(string $platform_order_no, string $upstream_order_no): bool
    {
        // TODO: Implement cancelOrder() method.
    }

    #[ArrayShape([
        'ok'     => 'bool',
        'origin' => 'string',
        'data'   => [
            '_upstream_order_no' => 'string',
            '_platform_order_no' => 'string',
            '_amount'            => 'float',
            '_pay_time'          => 'string',
            '_utr'               => 'string',
            '_payment_status'    => DisbursementOrderVerificationQueuePayStatus::class,
            '_rejection_reason'  => 'string',
        ]
    ])]
    public function notify(Request $request): array
    {
        // TODO: Implement notify() method.
    }

    public function notifyReturn(bool $success): Response
    {
        // TODO: Implement notifyReturn() method.
    }

}