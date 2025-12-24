<?php

namespace app\exception;
use Throwable;
class UploadException extends \RuntimeException
{
    public function __construct($message, $code = -1, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
