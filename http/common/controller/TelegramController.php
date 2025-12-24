<?php

namespace http\common\controller;

use app\controller\BasicController;
use app\lib\enum\ResultCode;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RequestMapping;
use app\router\Annotations\RestController;
use app\service\bot\TelegramService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/v1/common/telegram")]
class TelegramController extends BasicController
{
    #[Inject]
    protected TelegramService $service;

    #[PostMapping('/notify')]
    public function notify(): \support\Response
    {
        $url = env('APP_DOMAIN', 'https://server.yypay.cloud') . '/v1/common/telegram/webHook';
        return $this->success($this->service->notify($url));
    }

    #[PostMapping('/webHook')]
    public function webHook(Request $request): Response
    {
        $token = env('TELEGRAM_TOKEN');
        if (!$token){
            return $this->error(ResultCode::FAIL);
        }
        return $this->service->webHook($request->all()) ?   $this->success() : $this->error();
    }
}