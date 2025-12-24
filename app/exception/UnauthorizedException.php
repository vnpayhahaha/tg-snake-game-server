<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 签名信息错误
 * http state 401 Unauthorized:请求要求用户的身份认证
 */
class UnauthorizedException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::UNAUTHORIZED, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 401);
    }
}
