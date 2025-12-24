<?php

namespace http\openapi\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\TenantAccountService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/v1/api/tenant")]
class TenantAccountController extends BasicController
{
    #[Inject]
    protected TenantAccountService $service;

    // query balance
    #[PostMapping('/query_balance')]
    public function query_balance(Request $request): Response
    {
        $validator = validate($request->all(), [
            'tenant_id' => [
                'required',
                'string',
                'max:20',
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $tenant_account = $this->service->repository->getQuery()
            ->select([
                'tenant_id',
                'account_id',
                'balance_available',
                'balance_frozen',
                'account_type',
                'updated_at',
            ])
            ->where('tenant_id', $validatedData['tenant_id'])->get();
        return $this->success($tenant_account?->toArray());
    }
}
