<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\BankDisbursementBillIobOtherService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class BankDisbursementBillIobOtherController extends BasicController
{
    #[Inject]
    protected BankDisbursementBillIobOtherService $service;

    #[GetMapping('/bank_disbursement_bill_iob_other/list')]
    #[Permission(code: 'transaction:bank_disbursement_bill_iob_other:list')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            $this->service->page($request->all(), $this->getCurrentPage(), $this->getPageSize())
        );
    }
}
