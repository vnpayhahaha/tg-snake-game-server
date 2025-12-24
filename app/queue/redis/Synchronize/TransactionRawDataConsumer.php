<?php

namespace app\queue\redis\Synchronize;

use app\constants\TransactionRawData;
use app\service\TransactionRawDataService;
use DI\Attribute\Inject;
use support\Log;
use Webman\RedisQueue\Consumer;

class TransactionRawDataConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = TransactionRawData::TRANSACTION_RAW_DATA_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    #[Inject]
    protected TransactionRawDataService $service;

    // 消费
    // [
    //            'channel_id'      => [
    //                'required',
    //                'integer',
    //                'between:1,99999999999'
    //            ],
    //            'bank_account_id' => [
    //                'required',
    //                'integer',
    //                'between:1,99999999999'
    //            ],
    //            'content'         => [
    //                'required',
    //                'string',
    //                'max:65535',
    //            ],
    //            'source'          => 'required|string|max:255',
    //        ]
    public function consume($data)
    {
        var_dump('TransactionRawDataConsumer=====', $data);
        if (!$data) {
            Log::error('TransactionRawDataConsumer: 缺少参数', [$data]);
            return false;
        }
        Log::info('TransactionRawDataConsumer: 开始消费', [$data]);
        if (!is_array($data)) {
            Log::error('TransactionRawDataConsumer: 参数错误', [$data]);
            return false;
        }
        // 验证参数
        //             'channel_id'      => [
        //                'required',
        //                'integer',
        //                'between:1,99999999999'
        //            ],
        //            'bank_account_id' => [
        //                'required',
        //                'integer',
        //                'between:1,99999999999'
        //            ],
        //            'content'         => [
        //                'required',
        //                'string',
        //                'max:65535',
        //            ],
        //            'source'          => 'required|string|max:255',
        if (!isset($data['channel_id'], $data['bank_account_id'], $data['content'], $data['source'])) {
            Log::error('TransactionRawDataConsumer: 参数错误', [$data]);
            return false;
        }
        // 验证hash是否存在
        $hash = md5($data['content']);
        if ($find = $this->service->repository->getQuery()->where('hash', $hash)->first()) {
            $find->increment('repeat_count');
            Log::warning('TransactionRawDataConsumer: 数据已存在', [$find->toArray()]);
            return false;
        }
        $this->service->create($data);
        return true;
    }

    public function onConsumeFailure(\Throwable $e, $package)
    {
        Log::error('TransactionRawDataConsumer: 消费失败', [$e->getMessage(), $package]);
        var_dump('TransactionRawDataConsumer: 消费失败', [$e->getMessage(), $package]);
    }
}