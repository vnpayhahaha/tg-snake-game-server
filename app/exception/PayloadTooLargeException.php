<?php

namespace app\exception;

use app\lib\enum\ResultCode;

/**
 * 请求内容大小超过限制
 * http state 413 Payload Too Large:请求的实体过大，超过服务器愿意或能够处理的范围
 * 客户端上传的文件或数据量超出服务器限制
 */
class PayloadTooLargeException extends BusinessException
{
    public function __construct(ResultCode $code = ResultCode::PAYLOAD_TOO_LARGE, ?string $message = null, mixed $data = [])
    {
        parent::__construct($code, $message, $data, 413);
    }
}
