<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 资源不存在
 * http state 404 Not Found:请求的资源不存在
 */
class ResourceNotFoundException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::NOT_FOUND, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 404);
    }
}
