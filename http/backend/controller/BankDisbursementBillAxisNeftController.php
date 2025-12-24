<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\BankDisbursementBillAxisNeftService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class BankDisbursementBillAxisNeftController extends BasicController
{
    #[Inject]
    protected BankDisbursementBillAxisNeftService $service;

    #[GetMapping('/bank_disbursement_bill_axis_neft/list')]
    #[Permission(code: 'transaction:bank_disbursement_bill_axis_neft:list')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            $this->service->page($request->all(), $this->getCurrentPage(), $this->getPageSize())
        );
    }
}
