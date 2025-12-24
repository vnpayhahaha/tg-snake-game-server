<?php

namespace app\command;

use support\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use app\lib\cache\CacheCleanerService;

class CacheCleanerCommand extends Command
{
    protected static string $defaultName        = 'cache:test-clear';
    protected static string $defaultDescription = 'Test annotation cache clearing';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("<info>Testing annotation cache clearing...</info>");

        // 1. 添加测试缓存项
        $key = \app\process\CacheableProcessor::ANNOTATION_PREFIX . 'test_key';
        Cache::set($key, 'test_value', 3600);
        $output->writeln("Added test cache key: {$key}");

        // 2. 清理缓存
        $count = CacheCleanerService::clearAllAnnotationCache();
        $output->writeln("Cleared {$count} items");

        // 3. 验证清理结果
        $exists = Cache::has($key) ? 'exists' : 'not exists';
        $output->writeln("Test key now: {$exists}");

        return 0;
    }
}
