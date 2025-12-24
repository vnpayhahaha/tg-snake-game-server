<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TransactionQueueStatusService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class TransactionQueueStatusController extends BasicController
{
    #[Inject]
    protected TransactionQueueStatusService $service;

    #[GetMapping('/transaction_queue_status/list')]
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
