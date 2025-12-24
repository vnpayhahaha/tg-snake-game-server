<?php

namespace app\command;

use app\process\CacheableProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class CacheListenerCommand extends Command
{
    protected static string $defaultName        = 'cache:listeners';
    protected static string $defaultDescription = 'List all registered cache listeners';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $listeners = CacheableProcessor::getListeners();

        if (empty($listeners)) {
            $output->writeln('<comment>No cache listeners registered</comment>');
            return 0;
        }

        $table = new Table($output);
        $table->setHeaders(['Listener', 'Methods', 'Tracked Keys', 'Cache Group']);

        foreach ($listeners as $listener => $data) {
            $methods = implode("\n", $data['methods']);
            $keys = implode("\n", array_slice($data['cache_keys'], 0, 5));
            $group = $this->getGroupForListener($data['methods'][0]);

            if (count($data['cache_keys']) > 5) {
                $keys .= "\n... and " . (count($data['cache_keys']) - 5) . " more";
            }

            $table->addRow([$listener, $methods, $keys, $group]);
        }

        $table->render();

        $output->writeln("\n<info>Total listeners: " . count($listeners) . "</info>");
        return 0;
    }

    private function getGroupForListener(string $methodId): string
    {
        try {
            [$class, $method] = explode('::', $methodId);
            $refMethod = new \ReflectionMethod($class, $method);
            $attributes = $refMethod->getAttributes(\app\lib\annotation\Cacheable::class);

            if (!empty($attributes)) {
                $cacheable = $attributes[0]->newInstance();
                return $cacheable->group;
            }
        } catch (\Throwable $e) {
            // 忽略错误
        }

        return 'unknown';
    }
}
