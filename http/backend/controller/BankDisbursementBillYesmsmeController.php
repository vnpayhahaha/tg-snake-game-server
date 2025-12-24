<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\BankDisbursementBillYesmsmeService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class BankDisbursementBillYesmsmeController extends BasicController
{
    #[Inject]
    protected BankDisbursementBillYesmsmeService $service;

    #[GetMapping('/bank_disbursement_bill_yesmsme/list')]
    #[Permission(code: 'transaction:bank_disbursement_bill_yesmsme:list')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            $this->service->page($request->all(), $this->getCurrentPage(), $this->getPageSize())
        );
    }
}
