<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\BusinessException;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\model\enums\MenuStatus;
use app\model\enums\RoleStatus;
use app\repository\MenuRepository;
use app\repository\RoleRepository;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\UserService;
use DI\Attribute\Inject;
use Illuminate\Support\Arr;
use support\Request;
use support\Response;

#[RestController("/admin/permission")]
class PermissionController extends BasicController
{

    #[Inject]
    protected MenuRepository $menuRepository;

    #[Inject]
    protected RoleRepository $roleRepository;

    #[Inject]
    protected UserService $userService;

    /**
     * 获取当前用户菜单
     * @param Request $request
     * @return Response
     */
    #[GetMapping('/menus')]
    public function menus(Request $request): Response
    {
        return $this->success(
            data: $request->user->isSuperAdmin()
                ? $this->menuRepository->list([
                    'status'    => MenuStatus::Normal,
                    'children'  => true,
                    'parent_id' => 0,
                ])
                : $request->user->filterCurrentUser()
        );
    }

    /**
     * 获取当前用户角色
     * @param Request $request
     * @return Response
     */
    #[GetMapping('/roles')]
    public function roles(Request $request): Response
    {
        return $this->success(
            data: $request->user->isSuperAdmin()
                ? $this->roleRepository->list(['status' => RoleStatus::Normal])
                : $request->user->getRoles(['name', 'code', 'remark'])
        );
    }

    /**
     * 更新用户信息
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    #[PostMapping('/update')]
    public function update(Request $request): Response
    {
        $validator = validate($request->post(), [
            'nickname'                  => 'sometimes|string|max:255',
            'new_password'              => 'sometimes|confirmed|string|min:8',
            'new_password_confirmation' => 'sometimes|string|min:8',
            'old_password'              => ['sometimes', 'string'],
            'avatar'                    => 'sometimes|string|max:255',
            'signed'                    => 'sometimes|string|max:255',
            'backend_setting'           => 'sometimes|array',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $user = $request->user;
        if (Arr::exists($validatedData, 'new_password')) {
            if (!$user->verifyPassword(Arr::get($validatedData, 'old_password'))) {
                throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, trans('old_password_error', [], 'user'));
            }
            $validatedData['password'] = $validatedData['new_password'];
        }
        $this->userService->updateById($user->id, $validatedData);
        return $this->success();
    }
}
