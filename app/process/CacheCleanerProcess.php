<?php
namespace app\process;

use app\lib\cache\CacheCleanerService;
use support\Log;
use Workerman\Worker;

class CacheCleanerProcess
{
    public function onWorkerStart(Worker $worker): void
    {
        var_dump('cache cleaner process start');
        // 只在主进程执行
        if ($worker->id === 0) {
            Log::info("CacheCleanerProcess: Starting cache cleanup...");
            echo "CacheCleanerProcess: Starting cache cleanup...\n";

            try {
                $count = CacheCleanerService::clearAllAnnotationCache();
                Log::info("CacheCleanerProcess: Cleared {$count} items");
                echo "CacheCleanerProcess: Cleared {$count} items\n";
            } catch (\Throwable $e) {
                Log::error("Error: " . $e->getMessage());
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
}
