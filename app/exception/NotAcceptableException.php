<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 访问方式不可接受
 * http state 406 Not Acceptable:请求的资源不可接受
 * 客户端请求头中的Accept字段与服务器支持的格式不匹配
 */
class NotAcceptableException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::NOT_ACCEPTABLE, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 406);
    }
}
