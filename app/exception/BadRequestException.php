<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 非法请求异常,请求失败
 * http state 400 Bad Request:请求无效
 */
class BadRequestException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::BAD_REQUEST, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 400); // 400是客户端错误，但可根据需求调整
    }
}
