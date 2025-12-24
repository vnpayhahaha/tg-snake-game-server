<?php

namespace http\common\controller;

use app\constants\TransactionRawData;
use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use support\Request;
use support\Response;
use Webman\RedisQueue\Redis;

#[RestController("/test")]
class TestController extends BasicController
{

    #[GetMapping('/index')]
    public function index(Request $request): Response
    {
        $redisKey = TransactionRawData::TRANSACTION_RAW_DATA_QUEUE_NAME;
        $isOk = Redis::send($redisKey, [
            'data' => [
                'id' => 1,
                'name' => 'test',
                'age' => 18,
                'address' => '中国'
            ]
        ]);
        return $this->success([
            'isOk' => $isOk
        ]);
    }
}