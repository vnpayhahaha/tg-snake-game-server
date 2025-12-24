<?php

namespace app\lib\traits;


use Illuminate\Database\ConnectionResolverInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webman\Context;

trait HasContainer
{
    /**
     * Get the database connection for the model.
     */
    public function getConnection(): ConnectionInterface
    {
        $connectionName = $this->getConnectionName();
        $resolver = $this->getContainer()->get(ConnectionResolverInterface::class);
        return $resolver->connection($connectionName);
    }

    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->getContainer()->get(EventDispatcherInterface::class);
    }

    protected function getContainer(): ContainerInterface
    {
        return Context::getContainer();
    }
}
