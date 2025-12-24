<?php

namespace app\lib\annotation;

/**
 * 用户权限验证。
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Permission
{
    public const OPERATION_AND = 'and';

    public const OPERATION_OR = 'or';
    /**
     * @var null|string 菜单代码
     * @var string 过滤条件 为 OR 时，检查有一个通过则全部通过 为 AND 时，检查有一个不通过则全不通过
     */
    public function __construct(
        protected array|string $code,
        protected string $operation = self::OPERATION_AND,
    ) {}

    public function getCode(): array
    {
        return \is_array($this->code) ? $this->code : [$this->code];
    }

    public function getOperation(): string
    {
        return $this->operation;
    }
}
