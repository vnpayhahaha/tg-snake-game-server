<?php

namespace app\process\task;

use app\constants\TenantAccount;
use Webman\RedisQueue\Redis;
use Workerman\Crontab\Crontab;

class TransactionCrontab
{
    public function onWorkerStart(): void
    {
        // 每分钟执行一次 查询没有处理在交易并执行
        // D/T 可能面临修改执行时间，创建完成不走队列，用定时任务查询预计执行时间执行
        new Crontab('0 */2 * * * *', function(){
            echo date('Y-m-d H:i:s')."\n";
//            Redis::send(TenantAccount::TRANSACTION_CONSUMER_QUEUE_NAME, [
//                'id' => '2',
//                'name' => 'test',
//            ]);

        });


        // todo 交易失败订单处理 && 通知
    }
}
