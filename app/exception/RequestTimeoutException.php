<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 请求超时
 * http state 408 Request Timeout:请求超时
 */
class RequestTimeoutException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::REQUEST_TIMEOUT, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 408);
    }
}
