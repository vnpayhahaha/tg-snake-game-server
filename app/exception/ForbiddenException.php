<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 请求被禁止
 * http state 403 Forbidden:服务器已经理解请求，但是拒绝执行它
 */
class ForbiddenException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::FORBIDDEN, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 403);
    }
}
