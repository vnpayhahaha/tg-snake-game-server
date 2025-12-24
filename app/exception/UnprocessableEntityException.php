<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 服务器理解了客户端的请求，但无法完成请求，因为数据格式有误或不符合要求
 * http state 422 Unprocessable Entity:请求数据格式正确，但是由于含有语义错误，无法响应
 * 客户端提交的数据不符合服务器的验证规则（如必填项未填写、输入格式不正确）
 */
class UnprocessableEntityException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::UNPROCESSABLE_ENTITY, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 422);
    }
}
