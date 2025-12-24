<?php

namespace app\model\enums;

enum ScopeType: string
{

    // 所有
    case ALL = 'ALL';
    // 只根据部门过滤
    case DEPT_SELF = 'DEPT_SELF';

    // 只根据部门树过滤
    case DEPT_TREE = 'DEPT_TREE';

    // 只根据用户过滤
    case SELF = 'SELF';
    // 自定义部门过滤
    case CUSTOM_DEPT = 'CUSTOM_DEPT';
    // 自定义函数过滤
    case CUSTOM_FUNC = 'CUSTOM_FUNC';
}
