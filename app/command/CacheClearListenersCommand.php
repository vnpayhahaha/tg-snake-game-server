<?php

namespace app\command;

use app\process\CacheableProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearListenersCommand extends Command
{
    protected static string $defaultName        = 'cache:clear-listeners';
    protected static string $defaultDescription = 'Clear all registered cache listeners';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = count(CacheableProcessor::getListeners());
        CacheableProcessor::clearListeners();

        $output->writeln("<info>Cleared {$count} cache listeners</info>");
        return 0;
    }
}
