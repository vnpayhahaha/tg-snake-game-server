<?php

namespace app\exception;

use app\lib\enum\ResultCode;

class OpenApiException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::OPENAPI_SYSTEM_ERROR, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 200);
    }
}
