<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 冲突
 * http state 409 Conflict:请求与服务器当前状态冲突
 * 多个请求尝试更新相同资源，且更新内容相互冲突。
 * 并发修改同一资源导致数据不一致。
 */
class ConflictException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::CONFLICT, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 409);
    }
}
