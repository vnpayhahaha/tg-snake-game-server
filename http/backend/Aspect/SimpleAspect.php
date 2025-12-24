<?php

namespace http\backend\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class SimpleAspect extends AbstractAspect
{
    public array $annotations = [
        'http\\backend\\Controller\\*'
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        error_log('SimpleAspect is working');
        var_dump('SimpleAspect is working');
        return $proceedingJoinPoint->process();
    }
}
