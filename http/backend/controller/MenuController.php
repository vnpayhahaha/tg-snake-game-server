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
use app\service\MenuService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class MenuController extends BasicController
{
    #[Inject]
    protected MenuService $service;

    #[GetMapping('/menu/list')]
    #[Permission(code: 'permission:menu:index')]
    #[OperationLog('菜单列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(data: $this->service->getRepository()->list([
            'children'  => true,
            'parent_id' => 0,
        ]));
    }

    // create
    #[PostMapping('/menu')]
    #[Permission(code: 'permission:menu:create')]
    #[OperationLog('创建菜单')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'parent_id'             => 'sometimes|integer',
            'name'                  => 'required|string|max:255',
            'path'                  => 'sometimes|string|max:255',
            'component'             => 'sometimes|string|max:255',
            'redirect'              => 'sometimes|string|max:255',
            'status'                => 'sometimes|integer',
            'sort'                  => 'sometimes|integer',
            'remark'                => 'sometimes|string|max:255',
            'meta.title'            => 'required|string|max:255',
            'meta.i18n'             => 'sometimes|string|max:255',
            'meta.badge'            => 'sometimes|string|max:255',
            'meta.link'             => 'sometimes|string|max:255',
            'meta.icon'             => 'sometimes|string|max:255',
            'meta.affix'            => 'sometimes|boolean',
            'meta.hidden'           => 'sometimes|boolean',
            'meta.type'             => 'sometimes|string|max:255',
            'meta.cache'            => 'sometimes|boolean',
            'meta.breadcrumbEnable' => 'sometimes|boolean',
            'meta.copyright'        => 'sometimes|boolean',
            'meta.componentPath'    => 'sometimes|string|max:64',
            'meta.componentSuffix'  => 'sometimes|string|max:4',
            'meta.activeName'       => 'sometimes|string|max:255',
            'btnPermission'         => 'sometimes|array',
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

    // save
    #[PutMapping('/menu/{id}')]
    #[Permission(code: 'permission:menu:save')]
    #[OperationLog('编辑菜单')]
    public function save(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'parent_id'             => 'sometimes|integer',
            'name'                  => 'required|string|max:255',
            'path'                  => 'sometimes|string|max:255',
            'component'             => 'sometimes|string|max:255',
            'redirect'              => 'sometimes|string|max:255',
            'status'                => 'sometimes|integer',
            'sort'                  => 'sometimes|integer',
            'remark'                => 'sometimes|string|max:255',
            'meta.title'            => 'required|string|max:255',
            'meta.i18n'             => 'sometimes|string|max:255',
            'meta.badge'            => 'sometimes|string|max:255',
            'meta.link'             => 'sometimes|string|max:255',
            'meta.icon'             => 'sometimes|string|max:255',
            'meta.affix'            => 'sometimes|boolean',
            'meta.hidden'           => 'sometimes|boolean',
            'meta.type'             => 'sometimes|string|max:255',
            'meta.cache'            => 'sometimes|boolean',
            'meta.breadcrumbEnable' => 'sometimes|boolean',
            'meta.copyright'        => 'sometimes|boolean',
            'meta.componentPath'    => 'sometimes|string|max:64',
            'meta.componentSuffix'  => 'sometimes|string|max:4',
            'meta.activeName'       => 'sometimes|string|max:255',
            'btnPermission'         => 'sometimes|array',
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

    // delete
    #[DeleteMapping('/menu')]
    #[Permission(code: 'permission:menu:delete')]
    #[OperationLog('删除菜单')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }
}
