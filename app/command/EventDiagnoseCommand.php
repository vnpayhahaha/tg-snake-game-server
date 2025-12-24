<?php

namespace app\command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use app\process\CacheableProcessor;
use Webman\Event\Event;

class EventDiagnoseCommand extends Command
{
    protected static string $defaultName        = 'event:diagnose';
    protected static string $defaultDescription = 'Diagnose event system issues';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $listener = 'system_config_update';

        $output->writeln("Diagnosing event system for listener: {$listener}");

        // 1. 检查是否注册了监听器
        $isRegistered = CacheableProcessor::isListenerRegistered($listener);
        $output->writeln("Listener registered: " . ($isRegistered ? 'YES' : 'NO'));

        // 2. 检查事件系统是否注册
        $hasListener = Event::hasListener($listener);
        $output->writeln("Event system has listener: " . ($hasListener ? 'YES' : 'NO'));

        // 3. 手动触发事件
        $output->writeln("Manually triggering event...");
        Event::dispatch($listener, ['test' => 'value']);

        // 4. 检查监听器调用
        $output->writeln("Check logs for 'Event {$listener} received' message");

        return 0;
    }
}
