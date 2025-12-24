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
use app\service\TgGameGroupService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏群组管理控制器
 */
#[RestController("/admin/tg_game")]
class TgGameGroupController extends BasicController
{
    #[Inject]
    protected TgGameGroupService $service;

    /**
     * 游戏群组列表
     */
    #[GetMapping('/group/list')]
    #[Permission(code: 'tg_game:group:list')]
    #[OperationLog('游戏群组列表')]
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
     * 获取远程搜索数据（供下拉选择器使用）
     * 必须在 /group/{id} 之前
     */
    #[GetMapping('/group/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'config_id',
            'group_name',
            'status',
            'created_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 获取群组详情
     * 必须在具体路径（如 /group/remote）之后
     */
    #[GetMapping('/group/{id}')]
    #[Permission(code: 'tg_game:group:detail')]
    #[OperationLog('查看群组详情')]
    public function detail(Request $request, int $id): Response
    {
        $group = $this->service->findById($id);
        if (!$group) {
            return $this->error(ResultCode::NOT_FOUND, '群组不存在');
        }
        return $this->success(data: $group);
    }

    /**
     * 获取群组统计数据
     */
    #[GetMapping('/group/{id}/statistics')]
    #[Permission(code: 'tg_game:group:statistics')]
    #[OperationLog('查看群组统计')]
    public function statistics(Request $request, int $id): Response
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $stats = $this->service->getGroupStatistics($id, $dateStart, $dateEnd);
        return $this->success(data: $stats);
    }

    /**
     * 获取群组当前蛇身
     */
    #[GetMapping('/group/{id}/snake')]
    #[Permission(code: 'tg_game:group:snake')]
    #[OperationLog('查看群组蛇身')]
    public function getSnake(Request $request, int $id): Response
    {
        $snake = $this->service->getCurrentSnake($id);
        return $this->success(data: $snake);
    }

    /**
     * 创建游戏群组
     */
    #[PostMapping('/group')]
    #[Permission(code: 'tg_game:group:create')]
    #[OperationLog('创建游戏群组')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'config_id' => 'required|integer|exists:tg_game_group_config,id',
            'group_name' => 'required|string|max:100',
            'status' => 'required|integer|between:1,2',
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

    /**
     * 重置群组奖池
     */
    #[PostMapping('/group/{id}/reset_prize_pool')]
    #[Permission(code: 'tg_game:group:resetPrizePool')]
    #[OperationLog('重置群组奖池')]
    public function resetPrizePool(Request $request, int $id): Response
    {
        $result = $this->service->resetPrizePool($id);
        return $result['success'] ? $this->success() : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 更新游戏群组
     */
    #[PutMapping('/group/{id}')]
    #[Permission(code: 'tg_game:group:update')]
    #[OperationLog('更新游戏群组')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'group_name' => 'sometimes|string|max:100',
            'status' => 'sometimes|integer|between:1,2',
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

    /**
     * 恢复回收站中的群组
     */
    #[PutMapping('/group/recovery')]
    #[Permission(code: 'tg_game:group:recovery')]
    #[OperationLog('恢复群组')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * 删除游戏群组（软删除）
     */
    #[DeleteMapping('/group')]
    #[Permission(code: 'tg_game:group:delete')]
    #[OperationLog('删除游戏群组')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    /**
     * 真实删除群组（清空回收站）
     */
    #[DeleteMapping('/group/real_delete')]
    #[Permission(code: 'tg_game:group:realDelete')]
    #[OperationLog('清空回收站')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }
}
