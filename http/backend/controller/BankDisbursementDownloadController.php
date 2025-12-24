<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\BankDisbursementDownloadService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class BankDisbursementDownloadController extends BasicController
{
    #[Inject]
    protected BankDisbursementDownloadService $service;

    #[GetMapping('/bank_disbursement_download/list')]
    #[Permission(code: 'transaction:bank_disbursement_download:list')]
    #[OperationLog('银行账单下载记录列表')]
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

    #[PostMapping('/bank_disbursement_download/download/{id}')]
    public function download(Request $request, int $id): Response
    {
        return $this->service->download($id);
    }
}
