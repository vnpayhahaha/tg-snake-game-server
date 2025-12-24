<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\ChannelCallbackRecordService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/channel")]
class ChannelCallbackRecordController extends BasicController
{
    #[Inject]
    protected ChannelCallbackRecordService $service;

    #[GetMapping('/channel_callback_record/list')]
    #[Permission(code: 'channel:channel_callback_record:list')]
    #[OperationLog('渠道回调记录列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: $this->service->page(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }
}
