<?php

namespace app\exception;

use app\lib\enum\ResultCode;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 内部服务器错误
 * http state 503 Internal Server Error:服务器内部错误
 */
class BusinessException extends HttpException
{

    protected mixed $data;

    public function __construct(ResultCode $code = ResultCode::FAIL, ?string $message = null, mixed $data = [], int $httpStatusCode = 503)
    {
        $this->code = $code->value;
        $this->message = $message;
        if ($message === null) {
            $this->message = ResultCode::getMessage($code->value);
        }
        $this->data = $data;
        parent::__construct($httpStatusCode, $this->message, null, [], $this->code);
    }

}

