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
use app\service\TgGameGroupConfigService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏群组配置管理控制器
 */
#[RestController("/admin/tg_game")]
class TgGameGroupConfigController extends BasicController
{
    #[Inject]
    protected TgGameGroupConfigService $service;

    /**
     * 群组配置列表
     */
    #[GetMapping('/config/list')]
    #[Permission(code: 'tg_game:config:list')]
    #[OperationLog('群组配置列表')]
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
     * 获取活跃配置列表
     * 必须在 /config/{id} 之前
     */
    #[GetMapping('/config/active')]
    #[Permission(code: 'tg_game:config:active')]
    public function activeConfigs(Request $request): Response
    {
        $configs = $this->service->getActiveConfigs();
        return $this->success(data: $configs);
    }

    /**
     * 获取远程搜索数据
     * 必须在 /config/{id} 之前
     */
    #[GetMapping('/config/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'tenant_id',
            'tg_chat_id',
            'tg_group_name',
            'wallet_address',
            'status',
            'created_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 根据Telegram群组ID获取配置
     * 必须在 /config/{id} 之前
     */
    #[GetMapping('/config/by_tg_chat/{tgChatId}')]
    #[Permission(code: 'tg_game:config:byTgChat')]
    public function getByTgChatId(Request $request, int $tgChatId): Response
    {
        $config = $this->service->getByTgChatId($tgChatId);
        if (!$config) {
            return $this->error(ResultCode::NOT_FOUND, '配置不存在');
        }
        return $this->success(data: $config);
    }

    /**
     * 获取配置详情
     * 必须在具体路径之后
     */
    #[GetMapping('/config/{id}')]
    #[Permission(code: 'tg_game:config:detail')]
    #[OperationLog('查看配置详情')]
    public function detail(Request $request, int $id): Response
    {
        $config = $this->service->findById($id);
        if (!$config) {
            return $this->error(ResultCode::NOT_FOUND, '配置不存在');
        }
        return $this->success(data: $config);
    }

    /**
     * 检查钱包变更状态
     */
    #[GetMapping('/config/{id}/wallet_change_status')]
    #[Permission(code: 'tg_game:config:walletChangeStatus')]
    #[OperationLog('检查钱包变更状态')]
    public function walletChangeStatus(Request $request, int $id): Response
    {
        $status = $this->service->checkWalletChangeStatus($id);
        return $this->success(data: ['wallet_change_status' => $status]);
    }

    /**
     * 获取配置变更历史
     */
    #[GetMapping('/config/{id}/history')]
    #[Permission(code: 'tg_game:config:history')]
    #[OperationLog('查看配置变更历史')]
    public function history(Request $request, int $id): Response
    {
        $limit = (int)$request->input('limit', 20);
        $history = $this->service->getConfigHistory($id, $limit);
        return $this->success(data: $history);
    }

    /**
     * 创建群组配置
     */
    #[PostMapping('/config')]
    #[Permission(code: 'tg_game:config:create')]
    #[OperationLog('创建群组配置')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'tenant_id' => 'required|string|max:50',
            'tg_chat_id' => 'required|integer',
            'tg_group_name' => 'required|string|max:200',
            'wallet_address' => 'required|string|max:100',
            'min_bet_amount' => 'required|numeric|min:0',
            'snake_head_ticket' => 'required|string|max:10',
            'prize_match_count' => 'required|integer|min:1',
            'prize_ratio_jackpot' => 'required|numeric|between:0,100',
            'prize_ratio_range_match' => 'required|numeric|between:0,100',
            'prize_ratio_platform' => 'required|numeric|between:0,100',
            'status' => 'required|integer|between:1,2',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();

        // 验证奖金比例总和是否为100
        $totalRatio = $validatedData['prize_ratio_jackpot']
                    + $validatedData['prize_ratio_range_match']
                    + $validatedData['prize_ratio_platform'];
        if (abs($totalRatio - 100) > 0.01) {
            return $this->error(ResultCode::BAD_REQUEST, '奖金比例总和必须为100%');
        }

        $this->service->create(array_merge(
            $validatedData,
            [
                'created_by' => $request->user->id,
            ]
        ));

        return $this->success();
    }

    /**
     * 开始钱包变更
     */
    #[PostMapping('/config/{id}/start_wallet_change')]
    #[Permission(code: 'tg_game:config:startWalletChange')]
    #[OperationLog('开始钱包变更')]
    public function startWalletChange(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'new_wallet_address' => 'required|string|max:100',
            'cooldown_minutes' => 'required|integer|min:1|max:1440', // 最多24小时
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->startWalletChange(
            $id,
            $validatedData['new_wallet_address'],
            $validatedData['cooldown_minutes']
        );

        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 取消钱包变更
     */
    #[PostMapping('/config/{id}/cancel_wallet_change')]
    #[Permission(code: 'tg_game:config:cancelWalletChange')]
    #[OperationLog('取消钱包变更')]
    public function cancelWalletChange(Request $request, int $id): Response
    {
        $result = $this->service->cancelWalletChange($id);
        return $result['success']
            ? $this->success()
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 完成钱包变更
     */
    #[PostMapping('/config/{id}/complete_wallet_change')]
    #[Permission(code: 'tg_game:config:completeWalletChange')]
    #[OperationLog('完成钱包变更')]
    public function completeWalletChange(Request $request, int $id): Response
    {
        $result = $this->service->completeWalletChange($id);
        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 恢复回收站中的配置
     * 必须在 /config/{id} 之前
     */
    #[PutMapping('/config/recovery')]
    #[Permission(code: 'tg_game:config:recovery')]
    #[OperationLog('恢复配置')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * 更新群组配置
     * 参数化路由，必须在静态路由之后
     */
    #[PutMapping('/config/{id}')]
    #[Permission(code: 'tg_game:config:update')]
    #[OperationLog('更新群组配置')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'tg_group_name' => 'sometimes|string|max:200',
            'min_bet_amount' => 'sometimes|numeric|min:0',
            'snake_head_ticket' => 'sometimes|string|max:10',
            'prize_match_count' => 'sometimes|integer|min:1',
            'prize_ratio_jackpot' => 'sometimes|numeric|between:0,100',
            'prize_ratio_range_match' => 'sometimes|numeric|between:0,100',
            'prize_ratio_platform' => 'sometimes|numeric|between:0,100',
            'status' => 'sometimes|integer|between:1,2',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();

        // 如果更新了任何比例，验证总和
        if (isset($validatedData['prize_ratio_jackpot'])
            || isset($validatedData['prize_ratio_range_match'])
            || isset($validatedData['prize_ratio_platform'])) {

            $config = $this->service->findById($id);
            if (!$config) {
                return $this->error(ResultCode::NOT_FOUND, '配置不存在');
            }

            $jackpot = $validatedData['prize_ratio_jackpot'] ?? $config->prize_ratio_jackpot;
            $rangeMatch = $validatedData['prize_ratio_range_match'] ?? $config->prize_ratio_range_match;
            $platform = $validatedData['prize_ratio_platform'] ?? $config->prize_ratio_platform;
            $totalRatio = $jackpot + $rangeMatch + $platform;

            if (abs($totalRatio - 100) > 0.01) {
                return $this->error(ResultCode::BAD_REQUEST, '奖金比例总和必须为100%');
            }
        }

        $this->service->updateConfig($id, array_merge(
            $validatedData,
            [
                'updated_by' => $request->user->id,
            ]
        ), 1); // 来源：管理后台

        return $this->success();
    }

    /**
     * 删除群组配置
     */
    #[DeleteMapping('/config')]
    #[Permission(code: 'tg_game:config:delete')]
    #[OperationLog('删除群组配置')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    /**
     * 真实删除配置
     */
    #[DeleteMapping('/config/real_delete')]
    #[Permission(code: 'tg_game:config:realDelete')]
    #[OperationLog('清空回收站')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }
}
