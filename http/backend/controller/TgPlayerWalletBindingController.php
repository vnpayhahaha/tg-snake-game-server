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
use app\service\TgPlayerWalletBindingService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram玩家钱包绑定管理控制器
 */
#[RestController("/admin/tg_game")]
class TgPlayerWalletBindingController extends BasicController
{
    #[Inject]
    protected TgPlayerWalletBindingService $service;

    /**
     * 玩家钱包绑定列表
     */
    #[GetMapping('/wallet_binding/list')]
    #[Permission(code: 'tg_game:wallet_binding:list')]
    #[OperationLog('玩家钱包绑定列表')]
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

    /**
     * 获取远程搜索数据
     * 必须在 /wallet_binding/{id} 之前
     */
    #[GetMapping('/wallet_binding/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'group_id',
            'tg_user_id',
            'tg_username',
            'wallet_address',
            'created_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 根据Telegram用户ID查询绑定
     * 必须在 /wallet_binding/{id} 之前
     */
    #[GetMapping('/wallet_binding/by_tg_user/{groupId}/{tgUserId}')]
    #[Permission(code: 'tg_game:wallet_binding:byTgUser')]
    #[OperationLog('根据TG用户查询绑定')]
    public function getByTgUserId(Request $request, int $groupId, int $tgUserId): Response
    {
        $binding = $this->service->getUserByTgUserId($groupId, $tgUserId);
        if (!$binding) {
            return $this->error(ResultCode::NOT_FOUND, '绑定记录不存在');
        }
        return $this->success(data: $binding);
    }

    /**
     * 根据钱包地址查询绑定
     * 必须在 /wallet_binding/{id} 之前
     */
    #[GetMapping('/wallet_binding/by_wallet/{groupId}/{walletAddress}')]
    #[Permission(code: 'tg_game:wallet_binding:byWallet')]
    #[OperationLog('根据钱包地址查询绑定')]
    public function getByWalletAddress(Request $request, int $groupId, string $walletAddress): Response
    {
        $binding = $this->service->getUserByWalletAddress($groupId, $walletAddress);
        if (!$binding) {
            return $this->error(ResultCode::NOT_FOUND, '绑定记录不存在');
        }
        return $this->success(data: $binding);
    }

    /**
     * 获取群组内所有绑定
     * 必须在 /wallet_binding/{id} 之前
     */
    #[GetMapping('/wallet_binding/by_group/{groupId}')]
    #[Permission(code: 'tg_game:wallet_binding:byGroup')]
    #[OperationLog('查看群组绑定列表')]
    public function getByGroupId(Request $request, int $groupId): Response
    {
        $bindings = $this->service->getByGroupId($groupId);
        return $this->success(data: $bindings);
    }

    /**
     * 获取绑定详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/wallet_binding/{id}')]
    #[Permission(code: 'tg_game:wallet_binding:detail')]
    #[OperationLog('查看绑定详情')]
    public function detail(Request $request, int $id): Response
    {
        $binding = $this->service->findById($id);
        if (!$binding) {
            return $this->error(ResultCode::NOT_FOUND, '绑定记录不存在');
        }
        return $this->success(data: $binding);
    }

    /**
     * 获取玩家的参与节点
     */
    #[GetMapping('/wallet_binding/{id}/nodes')]
    #[Permission(code: 'tg_game:wallet_binding:nodes')]
    #[OperationLog('查看玩家参与节点')]
    public function getPlayerNodes(Request $request, int $id): Response
    {
        $binding = $this->service->findById($id);
        if (!$binding) {
            return $this->error(ResultCode::NOT_FOUND, '绑定记录不存在');
        }

        $nodes = $this->service->getPlayerNodes($binding->group_id, $binding->tg_user_id);
        return $this->success(data: $nodes);
    }

    /**
     * 获取玩家统计信息
     */
    #[GetMapping('/wallet_binding/{id}/statistics')]
    #[Permission(code: 'tg_game:wallet_binding:statistics')]
    #[OperationLog('查看玩家统计')]
    public function getPlayerStatistics(Request $request, int $id): Response
    {
        $binding = $this->service->findById($id);
        if (!$binding) {
            return $this->error(ResultCode::NOT_FOUND, '绑定记录不存在');
        }

        $stats = $this->service->getPlayerStatistics($binding->group_id, $binding->tg_user_id);
        return $this->success(data: $stats);
    }

    /**
     * 获取绑定变更日志
     */
    #[GetMapping('/wallet_binding/{id}/logs')]
    #[Permission(code: 'tg_game:wallet_binding:logs')]
    #[OperationLog('查看绑定变更日志')]
    public function getLogs(Request $request, int $id): Response
    {
        $binding = $this->service->findById($id);
        if (!$binding) {
            return $this->error(ResultCode::NOT_FOUND, '绑定记录不存在');
        }

        $limit = (int)$request->input('limit', 20);
        $logs = $this->service->getBindingLogs($binding->group_id, $binding->tg_user_id, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 创建钱包绑定（手动创建）
     */
    #[PostMapping('/wallet_binding')]
    #[Permission(code: 'tg_game:wallet_binding:create')]
    #[OperationLog('创建钱包绑定')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'group_id' => 'required|integer|exists:tg_game_group_config,id',
            'tg_user_id' => 'required|integer',
            'tg_username' => 'sometimes|string|max:100',
            'wallet_address' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->bindWallet(
            $validatedData['group_id'],
            $validatedData['tg_user_id'],
            $validatedData['tg_username'] ?? null,
            $validatedData['wallet_address']
        );

        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 批量导入绑定关系
     * 必须在 /wallet_binding/{id} 之前
     */
    #[PostMapping('/wallet_binding/batch_import')]
    #[Permission(code: 'tg_game:wallet_binding:batchImport')]
    #[OperationLog('批量导入绑定关系')]
    public function batchImport(Request $request): Response
    {
        $validator = validate($request->all(), [
            'group_id' => 'required|integer|exists:tg_game_group_config,id',
            'bindings' => 'required|array',
            'bindings.*.tg_user_id' => 'required|integer',
            'bindings.*.tg_username' => 'sometimes|string|max:100',
            'bindings.*.wallet_address' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->batchBindWallets(
            $validatedData['group_id'],
            $validatedData['bindings']
        );

        return $this->success(data: $result);
    }

    /**
     * 解除绑定
     */
    #[PostMapping('/wallet_binding/{id}/unbind')]
    #[Permission(code: 'tg_game:wallet_binding:unbind')]
    #[OperationLog('解除钱包绑定')]
    public function unbind(Request $request, int $id): Response
    {
        $binding = $this->service->findById($id);
        if (!$binding) {
            return $this->error(ResultCode::NOT_FOUND, '绑定记录不存在');
        }

        $result = $this->service->unbindWallet(
            $binding->group_id,
            $binding->tg_user_id
        );

        return $result['success']
            ? $this->success()
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 恢复回收站中的绑定
     * 必须在 /wallet_binding/{id} 之前
     */
    #[PutMapping('/wallet_binding/recovery')]
    #[Permission(code: 'tg_game:wallet_binding:recovery')]
    #[OperationLog('恢复绑定记录')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * 更新绑定信息
     * 参数化路由，必须在静态路由之后
     */
    #[PutMapping('/wallet_binding/{id}')]
    #[Permission(code: 'tg_game:wallet_binding:update')]
    #[OperationLog('更新绑定信息')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'tg_username' => 'sometimes|string|max:100',
            'wallet_address' => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $this->service->updateById($id, $validatedData);

        return $this->success();
    }

    /**
     * 删除绑定记录
     */
    #[DeleteMapping('/wallet_binding')]
    #[Permission(code: 'tg_game:wallet_binding:delete')]
    #[OperationLog('删除绑定记录')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    /**
     * 真实删除绑定
     */
    #[DeleteMapping('/wallet_binding/real_delete')]
    #[Permission(code: 'tg_game:wallet_binding:realDelete')]
    #[OperationLog('清空回收站')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }
}
