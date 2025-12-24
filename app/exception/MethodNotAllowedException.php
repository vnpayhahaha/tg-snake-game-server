<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 访问方式不允许
 * http state 405 Method Not Allowed:请求方法不允许
 */
class MethodNotAllowedException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::METHOD_NOT_ALLOWED, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 405);
    }
}
