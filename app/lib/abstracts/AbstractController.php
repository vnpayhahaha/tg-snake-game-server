<?php

namespace app\lib\abstracts;

use app\lib\enum\ResultCode;
use support\Context;
use support\Response;
use Webman\Http\Request;

abstract class AbstractController
{
    protected function success(mixed $data = null, ?string $message = null): Response
    {

        return $this->json(ResultCode::SUCCESS, $message, $data);
    }

    protected function error(ResultCode $code = ResultCode::FAIL, ?string $message = null, mixed $data = null, int $httpStatus = 500): Response
    {
        return $this->json($code, $message, $data, $httpStatus);
    }

    protected function json(ResultCode $code = ResultCode::SUCCESS, ?string $message = null, mixed $data = null, int $httpStatus = 200): Response
    {
        $request = Context::get(Request::class);
        $resultData = [
            'request_id' => $request->requestId,
            'path'       => $request->path(),
            'success'    => $code->value === ResultCode::SUCCESS->value,
            'code'       => $code->value,
            'message'    => $message ?? ResultCode::getMessage($code->value),
        ];
        if ($data !== null) {
            $resultData['data'] = $data;
        }
        return new Response($httpStatus, ['Content-Type' => 'application/json'], json_encode($resultData));
    }

    protected function getRequest(): \support\Request|\Webman\Http\Request|null
    {
        return \request();
    }

}
