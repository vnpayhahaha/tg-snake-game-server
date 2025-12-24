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
use app\service\ChannelService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/channel")]
class ChannelController extends BasicController
{
    #[Inject]
    protected ChannelService $service;

    #[GetMapping('/channel/list')]
    #[Permission(code: 'channel:channel:list')]
    #[OperationLog('渠道管理列表')]
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
    #[DeleteMapping('/channel/real_delete')]
    #[Permission(code: 'channel:channel:realDelete')]
    #[OperationLog('清空渠道')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    // 单个或批量恢复在回收站的数据
    #[PutMapping('/channel/recovery')]
    #[Permission(code: 'channel:channel:recovery')]
    #[OperationLog('渠道回收站恢复')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    #[PostMapping('/channel')]
    #[Permission(code: 'channel:channel:create')]
    #[OperationLog('创建渠道')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'channel_code'         => 'required|string|max:50',
            'channel_name'         => 'required|string|max:100',
            'channel_type'         => ['required', 'integer', 'between:1,2'],
            'country_code'         => 'required|string|max:10',
            'currency'             => 'required|string|max:3',
            'support_collection'   => ['required', 'boolean'],
            'support_disbursement' => ['required', 'boolean'],
            'status'               => ['required', 'boolean'],
            'config'               => ['array']
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

    #[PutMapping('/channel/{id}')]
    #[Permission(code: 'channel:channel:update')]
    #[OperationLog('编辑渠道')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'channel_code'         => 'required|string|max:50',
            'channel_name'         => 'required|string|max:100',
            'channel_type'         => ['required', 'integer', 'between:1,2'],
            'country_code'         => 'required|string|max:10',
            'currency'             => 'required|string|max:3',
            'support_collection'   => ['required', 'boolean'],
            'support_disbursement' => ['required', 'boolean'],
            'status'               => ['required', 'boolean'],
            'config'               => ['array']
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

    #[DeleteMapping('/channel')]
    #[Permission(code: 'channel:channel:delete')]
    #[OperationLog('删除渠道')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    #[GetMapping('/channel_dict/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'channel_code',
            'channel_name',
            'channel_type',
            'currency',
            'status',
            'support_collection',
            'support_disbursement',
            'config'
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

}
