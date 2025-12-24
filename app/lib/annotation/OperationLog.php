<?php

namespace app\lib\annotation;

use Attribute;


/**
 * 记录操作日志注解。
 */
#[Attribute(Attribute::TARGET_METHOD)]
class OperationLog
{
    /**
     * 菜单名称.
     * @var null|string
     */
    public function __construct(public ?string $name = null) {}
}
