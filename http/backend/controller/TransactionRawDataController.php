<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\TransactionRawDataService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class TransactionRawDataController extends BasicController
{
    #[Inject]
    protected TransactionRawDataService $service;

    #[GetMapping('/transaction_raw_data/list')]
    #[Permission(code: 'transaction:transaction_raw_data:list')]
    #[OperationLog('凭证原始数据列表')]
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

    // create
    #[PostMapping('/transaction_raw_data')]
    #[Permission(code: 'transaction:transaction_raw_data:create')]
    #[OperationLog('创建凭证原始数据')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'channel_id'      => [
                'required',
                'integer',
                'between:1,99999999999'
            ],
            'bank_account_id' => [
                'required',
                'integer',
                'between:1,99999999999'
            ],
            'content'         => [
                'required',
                'string',
                'max:65535',
            ],
            'source'          => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        // 验证hash是否存在
        $hash = md5($validatedData['content']);
        if ($find = $this->service->repository->getQuery()->where('hash', $hash)->first()) {
            $find->increment('repeat_count');
            return $this->error(ResultCode::UNPROCESSABLE_ENTITY, trans('unique', [':attribute' => 'content'], 'validation'));
        }
        $this->service->create($validatedData);
        return $this->success();
    }


    #[PutMapping('/transaction_raw_data/{id}')]
    #[Permission(code: 'transaction:transaction_raw_data:update')]
    #[OperationLog('编辑交易原始数据')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'channel_id'      => [
                'required',
                'integer',
                'between:1,99999999999'
            ],
            'bank_account_id' => [
                'required',
                'integer',
                'between:1,99999999999'
            ],
            'content'         => 'required|string|max:9999',
            'source'          => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->updateById($id, $validatedData);
        return $this->success();
    }
}
