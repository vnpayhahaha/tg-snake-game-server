<?php

namespace app\upstream\Handle;

use support\Container;

class TransactionCollectionOrderFactory
{
    private static $instances = [];

    public static function getInstance(string $className): TransactionCollectionOrderInterface
    {
        // 如果是 /，先转换成 \ ,例如  App/Transaction/Service =》 App\Transaction\Service
        $className = str_replace('/', '\\', $className);
        if (!isset(self::$instances[$className])) {
            // 使用容器来创建实例，这样可以启用依赖注入
            self::$instances[$className] = Container::make($className);
        }
        return self::$instances[$className];
    }

}
