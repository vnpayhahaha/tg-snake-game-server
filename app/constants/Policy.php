<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class Policy
{
    use ConstantsOptionTrait;

    // 策略类型（DEPT_SELF, DEPT_TREE, ALL, SELF, CUSTOM_DEPT, CUSTOM_FUNC）.
    public const TYPE_DEPT_SELF   = 'DEPT_SELF';
    public const TYPE_DEPT_TREE   = 'DEPT_TREE';
    public const TYPE_ALL         = 'ALL';
    public const TYPE_SELF        = 'SELF';
    public const TYPE_CUSTOM_DEPT = 'CUSTOM_DEPT';
    public const TYPE_CUSTOM_FUNC = 'CUSTOM_FUNC';
    public static array $type_list = [
        self::TYPE_DEPT_SELF   => 'DEPT_SELF',
        self::TYPE_DEPT_TREE   => 'DEPT_TREE',
        self::TYPE_ALL         => 'ALL',
        self::TYPE_SELF        => 'SELF',
        self::TYPE_CUSTOM_DEPT => 'CUSTOM_DEPT',
        self::TYPE_CUSTOM_FUNC => 'CUSTOM_FUNC',
    ];
}
