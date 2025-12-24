<?php

namespace http\backend\controller;

use app\constants\User;
use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\model\enums\PolicyType;
use app\model\ModelRole;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\RoleService;
use app\service\UserService;
use DI\Attribute\Inject;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use PragmaRX\Google2FA\Google2FA;
use support\Request;
use support\Response;

#[RestController("/admin")]
class UserController extends BasicController
{

    #[Inject]
    protected UserService $userService;

    #[Inject]
    protected RoleService $roleService;

    #[Inject]
    protected Google2FA $google2FA;

    #[GetMapping('/user/list')]
    #[Permission(code: 'permission:user:index')]
    #[OperationLog('用户列表')]
    public function pageList(Request $request): Response
    {

        return $this->success(
            data: $this->userService->page(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }

    #[PutMapping('/user')]
    #[Permission(code: 'permission:user:update')]
    #[OperationLog('更新用户信息')]
    public function updateInfo(Request $request): Response
    {
        $validator = validate($request->post(), [
            'username'           => 'required|string|max:20',
            'user_type'          => 'required|integer',
            'nickname'           => [
                'required',
                'string',
                'max:60',
                'regex:/^[^\s]+$/'
            ],
            'phone'              => 'sometimes|string|max:12',
            'email'              => 'sometimes|string|max:60|email:rfc,dns',
            'avatar'             => 'sometimes|string|max:255|url',
            'signed'             => 'sometimes|string|max:255',
            'status'             => 'sometimes|integer',
            'backend_setting'    => 'sometimes|array|max:255',
            'remark'             => 'sometimes|string|max:255',
            'policy'             => 'sometimes|array',
            'policy.policy_type' => [
                'required_with:policy',
                'string',
                'max:20',
                Rule::enum(PolicyType::class),
            ],
            'policy.value'       => [
                'sometimes',
            ],
            'department'         => [
                'sometimes',
                'array',
            ],
            'department.*'       => [
                'required_with:department',
                'integer',
                'exists:department,id',
            ],
            'position'           => [
                'sometimes',
                'array',
            ],
            'position.*'         => [
                'sometimes',
                'integer',
                'exists:position,id',
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->userService->updateById($request->user->id, Arr::except($validatedData, ['password']));
        return $this->success();
    }

    /**
     * 重置密码
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    #[PutMapping('/user/password')]
    #[Permission(code: 'permission:user:password')]
    #[OperationLog('重置密码')]
    public function resetPassword(Request $request): Response
    {
        $validator = validate($request->all(), [
            'id' => 'required|integer|between:1,4294967295',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        return $this->userService->resetPassword($validatedData['id'])
            ? $this->success()
            : $this->error();
    }

    // 绑定google密钥
    #[PutMapping('/bindGoogleSecretKey')]
    public function bindGoogleSecretKey(Request $request): Response
    {
        $validator = validate($request->all(), [
            'google_secret'  => 'required|string',
            'is_bind_google' => 'required|boolean',
            'code'           => 'required|string'
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $is_pass = $this->google2FA->verifyKey($validatedData['google_secret'], $validatedData['code']);
        if ($is_pass) {
            unset($validatedData['code']);
            return $this->userService->repository->getQuery()->where('id', $request->user->id)->update($validatedData) > 0 ?
                $this->success() :
                $this->error(ResultCode::USER_GOOGLE_2FA_VERIFY_FAILED);
        }
        return $this->error(ResultCode::USER_GOOGLE_2FA_VERIFY_FAILED);
    }

    // 重置google密钥
    #[PutMapping('/user/resetGoogle2FaSecret/{id}')]
    public function resetGoogle2FaSecret(Request $request, int $id): Response
    {
        return $this->userService->repository->getQuery()->where('id', $id)->update([
            'is_enabled_google' => User::GOOGLE_STATUS_DISABLE,
            'is_bind_google'    => User::GOOGLE_BIND_NO,
            'google_secret'     => '',
        ]) > 0 ? $this->success() : $this->error();
    }

    // 更新google验证状态
    #[PutMapping('/user/google_2fa_status')]
    public function updateGoogle2FAStatus(Request $request): Response
    {
        $validator = validate($request->all(), [
            'is_enabled_google' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $res = $this->userService->repository->getQuery()
            ->where('id', $request->user->id)
            ->where('is_enabled_google', !$validatedData['is_enabled_google'])
            ->update($validatedData);

        return $res > 0 ? $this->success() : $this->error();
    }

    // create
    #[PostMapping('/user')]
    #[Permission(code: 'permission:user:save')]
    #[OperationLog('创建用户')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'username'              => 'required|string|max:20',
            'user_type'             => 'required|integer',
            'nickname'              => [
                'required',
                'string',
                'max:60',
                'regex:/^[^\s]+$/'
            ],
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required| min:6 | max:20',
            'phone'                 => 'sometimes|string|max:12',
            'email'                 => 'sometimes|string|max:60|email:rfc,dns',
            'avatar'                => 'sometimes|string|max:255|url',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->userService->create(array_merge(
            $validatedData,
            [
                'created_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    // save
    #[PutMapping('/user/{userId}')]
    #[Permission(code: 'permission:user:update')]
    #[OperationLog('更新用户')]
    public function save(Request $request, int $userId): Response
    {
        $validator = validate($request->all(), [
            'username'  => 'required|string|max:20',
            'user_type' => 'required|integer',
            'nickname'  => [
                'required',
                'string',
                'max:60',
                'regex:/^[^\s]+$/'
            ],
            'phone'     => 'sometimes|string|max:12',
            'email'     => 'sometimes|string|max:60|email:rfc,dns',
            'avatar'    => 'sometimes|string|max:255|url',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
//        $validatedData = $validator->validate();
        $this->userService->updateById($userId, array_merge(
            $request->all(),
            [
                'updated_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    // delete
    #[DeleteMapping('/user')]
    #[Permission(code: 'permission:user:delete')]
    #[OperationLog('删除用户')]
    public function delete(Request $request): Response
    {
        $this->userService->deleteById($request->all());
        return $this->success();
    }

    // 获取用户角色列表
    #[GetMapping('/user/{userId}/roles')]
    #[Permission(code: 'permission:user:getRole')]
    #[OperationLog('获取用户角色列表')]
    public function getUserRoles(int $userId): Response
    {
        return $this->success($this->userService->getUserRoles($userId)->map(static fn(ModelRole $role) => $role->only([
            'id',
            'code',
            'name',
        ])));
    }

    // 批量授权用户角色
    #[PutMapping('/user/{userId}/roles')]
    #[Permission(code: 'permission:user:setRole')]
    #[OperationLog('批量授权用户角色')]
    public function batchGrantUserRoles(Request $request, int $userId): Response
    {
        $validator = validate($request->all(), [
            'role_codes'   => 'required|array',
            'role_codes.*' => [
                'string',
                function ($attribute, $value, $fail) {
                    if (!$this->roleService->repository->getModel()->where('code', $value)->exists()) {
                        $fail(trans('exists', [':attribute' => $attribute], 'validation'));
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->userService->batchGrantRoleForUser($userId, $validatedData['role_codes']);
        return $this->success();
    }

    // remote
    #[GetMapping('/userDict/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'username',
            'user_type',
            'nickname',
            'status',
            'login_ip',
            'login_time',
        ];
        return $this->success(
            $this->userService->getList([])->map(static fn($user) => $user->only($fields))
        );
    }

}
