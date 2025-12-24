<?php

namespace app\model;

use support\Model;

class BasicModel extends Model
{
    /**
     * 状态
     */
    public const ENABLE = 1;

    public const DISABLE = 2;

    /**
     * 默认每页记录数.
     */
    public const PAGE_SIZE = 15;
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

}
