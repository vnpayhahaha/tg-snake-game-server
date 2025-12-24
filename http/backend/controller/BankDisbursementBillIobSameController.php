<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\BankDisbursementBillIobSameService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class BankDisbursementBillIobSameController extends BasicController
{
    #[Inject]
    protected BankDisbursementBillIobSameService $service;

    #[GetMapping('/bank_disbursement_bill_iob_same/list')]
    #[Permission(code: 'transaction:bank_disbursement_bill_iob_same:list')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            $this->service->page($request->all(), $this->getCurrentPage(), $this->getPageSize())
        );
    }
}
