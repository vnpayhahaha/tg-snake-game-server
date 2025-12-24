<?php

namespace app\exception\Handler;

use app\lib\enum\ResultCode;
use support\Context;
use support\exception\Handler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

class AppExceptionHandle extends Handler
{
    public function render(Request $request, Throwable $e): Response
    {
        // 判断异常类型，进行不同的处理
        if ($e instanceof HttpException) {
            return $this->renderHttpException($e);
        }

        return $this->renderOtherException($e);
    }

    protected function renderHttpException(HttpException $e): Response
    {
        $statusCode = $e->getStatusCode();
        $data = config('app.debug') ? [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTrace(),
        ] : null;
        $request = Context::get(Request::class);
        $resultData = [
            'request_id' => $request->requestId,
            'path'       => $request->path(),
            'success'    => false,
            'code'       => ResultCode::from($e->getCode()),
            'message'    => $e->getMessage() ?? ResultCode::getMessage($e->getCode()),
        ];
        if ($data !== null) {
            $resultData['data'] = $data;
        }
        return new \support\Response($statusCode, ['Content-Type' => 'application/json'], json_encode($resultData));
    }

    protected function renderOtherException(Throwable $e): Response
    {
        $data = config('app.debug') ? [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTrace(),
        ] : null;

        $getCode = (int)$e->getCode();
        $statusCode = 500;
        if (in_array($getCode, [
            500,
            501,
            502,
            503,
            400,
            401,
            402,
            403,
            404,
            405,
            406,
            408,
            409,
            422
        ])) {
            $statusCode = $getCode;
        }
        $request = Context::get(Request::class);

        $errorCode = match ($statusCode) {
            400 => ResultCode::BAD_REQUEST,
            401 => ResultCode::UNAUTHORIZED,
            402 => ResultCode::TOKEN_REFRESH_EXPIRED,
            403 => ResultCode::FORBIDDEN,
            404 => ResultCode::NOT_FOUND,
            405 => ResultCode::METHOD_NOT_ALLOWED,
            406 => ResultCode::NOT_ACCEPTABLE,
            408 => ResultCode::REQUEST_TIMEOUT,
            409 => ResultCode::CONFLICT,
            422 => ResultCode::UNPROCESSABLE_ENTITY,
            default => ResultCode::UNKNOWN,
        };
        $resultData = [
            'request_id' => $request->requestId,
            'path'       => $request->path(),
            'success'    => false,
            'code'       => $errorCode,
            'message'    => $e->getMessage() ?? ResultCode::getMessage($errorCode->value),
        ];
        if ($data !== null) {
            $resultData['data'] = $data;
        }
        return new Response($statusCode, ['Content-Type' => 'application/json'], json_encode($resultData));
    }
}
