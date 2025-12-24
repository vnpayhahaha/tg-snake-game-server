<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\ChannelAccountService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/channel")]
class ChannelAccountController extends BasicController
{
    #[Inject]
    protected ChannelAccountService $service;

    #[GetMapping('/channel_account/list')]
    #[Permission(code: 'channel:channel_account:list')]
    #[OperationLog('渠道账户管理列表')]
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

    // 单个或批量真实删除数据 （清空回收站）
    #[DeleteMapping('/channel_account/real_delete')]
    #[Permission(code: 'channel:channel_account:real_delete')]
    #[OperationLog('清空渠道账户')]
    public function real_delete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    // 单个或批量恢复在回收站的数据
    #[PutMapping('/channel_account/recovery')]
    #[Permission(code: 'channel:channel_account:recovery')]
    #[OperationLog('渠道账户回收站恢复')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    #[PostMapping('/channel_account')]
    #[Permission(code: 'channel:channel_account:create')]
    #[OperationLog('创建渠道账户')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'channel_id'              => ['required', 'integer', 'between:1,99999999999'],
            'merchant_id'             => [
                'required',
                'string',
                'max:50',
                // channel_id merchant_id
                function ($attribute, $value, $fail) use ($request) {
                    if ($this->service->repository->getModel()
                        ->where('channel_id', $request->input('channel_id'))
                        ->where('merchant_id', $value)
                        ->exists()
                    ) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'limit_quota'             => 'required|numeric|between:0,999999999',
            'status'                  => ['required', 'boolean'],
            'support_collection'      => ['required', 'boolean'],
            'support_disbursement'    => ['required', 'boolean'],
            'daily_max_receipt'       => 'required|numeric|between:0,999999999',
            'daily_max_payment'       => 'required|numeric|between:0,999999999',
            'daily_max_receipt_count' => 'required|integer|between:0,999999999',
            'daily_max_payment_count' => 'required|integer|between:0,999999999',
            'max_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'max_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'min_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'min_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'api_config'              => ['array'],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->create(array_merge(
            $validatedData,
            [
                'created_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    #[PutMapping('/channel_account/{id}')]
    #[Permission(code: 'channel:channel_account:update')]
    #[OperationLog('编辑渠道账户')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'channel_id'              => ['required', 'integer', 'between:1,99999999999'],
            'merchant_id'             => [
                'required',
                'string',
                'max:50',
                // channel_id merchant_id
                function ($attribute, $value, $fail) use ($request, $id) {
                    $channelAccount = $this->service->repository->getModel()
                        ->where('channel_id', $request->input('channel_id'))
                        ->where('merchant_id', $value)
                        ->where('id', '<>', $id)
                        ->exists();
                    if ($channelAccount) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'limit_quota'             => 'required|numeric|between:0,999999999',
            'status'                  => ['required', 'boolean'],
            'support_collection'      => ['required', 'boolean'],
            'support_disbursement'    => ['required', 'boolean'],
            'daily_max_receipt'       => 'required|numeric|between:0,999999999',
            'daily_max_payment'       => 'required|numeric|between:0,999999999',
            'daily_max_receipt_count' => 'required|integer|between:0,999999999',
            'daily_max_payment_count' => 'required|integer|between:0,999999999',
            'max_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'max_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'min_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'min_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'api_config'              => ['array'],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->updateById($id, array_merge(
            $validatedData,
            [
                'updated_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    #[DeleteMapping('/channel_account')]
    #[Permission(code: 'channel:channel_account:delete')]
    #[OperationLog('删除渠道账户')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    #[GetMapping('/channel_account/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'channel_id',
            'merchant_id',
            'currency',
            'status',
            'support_collection',
            'support_disbursement',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    // 可用选项 type 1 代收 2 代付
    #[GetMapping('/channel_account/available_options/{type}')]
    public function options(Request $request, int $type): Response
    {
        $fields = [
            'id',
            'merchant_id',
        ];

        $channelAccounts = $this->service->repository->getQuery()
            ->whereHas('channel', function ($query) use ($type) {
                $query->where('status', true);
                if ($type === 1) {
                    $query->where('support_collection', true);
                } else {
                    $query->where('support_disbursement', true);
                }
            })
            ->where('status', true)
            ->where(function ($query) use ($type) {
                if ($type === 1) {
                    $query->where('support_collection', true);
                } else {
                    $query->where('support_disbursement', true);
                }
            })
            ->get()
            ->map(static fn($model) => $model->only($fields));

        return $this->success(
            $channelAccounts
        );
    }

}
