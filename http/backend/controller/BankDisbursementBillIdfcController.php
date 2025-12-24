<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\BankDisbursementBillIdfcService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class BankDisbursementBillIdfcController extends BasicController
{
    #[Inject]
    protected BankDisbursementBillIdfcService $service;

    #[GetMapping('/bank_disbursement_bill_idfc/list')]
    #[Permission(code: 'transaction:bank_disbursement_bill_idfc:list')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            $this->service->page($request->all(), $this->getCurrentPage(), $this->getPageSize())
        );
    }
}
