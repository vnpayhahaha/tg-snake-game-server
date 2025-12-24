<?php

namespace app\queue\redis\failed;


use support\Redis;
use Workerman\RedisQueue\Client;
use Workerman\Timer;

class FailedConsumer
{
    public function onWorkerStart()
    {
        // 每隔10秒检查
        Timer::add(10, function(){
//            RedisQueue::QUEUE_FAILED;
            $failedKey = Client::QUEUE_FAILED;
            $getRedis = Redis::connection('queue')->rPop($failedKey);
            var_dump('每隔10秒检查一次数据:'.$failedKey,$getRedis);
        });
    }
}
