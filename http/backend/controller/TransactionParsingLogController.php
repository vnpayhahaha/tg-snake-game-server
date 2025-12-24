<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TransactionParsingLogService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class TransactionParsingLogController extends BasicController
{
    #[Inject]
    protected TransactionParsingLogService $service;

    #[GetMapping('/transaction_parsing_log/list')]
    #[Permission(code: 'transaction:transaction_parsing_log:list')]
    #[OperationLog('凭证解析记录列表')]
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
