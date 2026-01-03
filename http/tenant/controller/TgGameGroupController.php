<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use http\tenant\Service\TgGameGroupConfigService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * 租户端TG游戏群组管理控制器
 */
#[RestController("/tenant/tg_game_group")]
class TgGameGroupController extends BasicController
{
    #[Inject]
    protected TgGameGroupConfigService $configService;

    /**
     * 获取群组列表
     */
    #[GetMapping('/list')]
    public function list(Request $request): Response
    {
        $tenantId = $request->user->tenant_id;
        $params = $request->all();
        $page = $this->getCurrentPage();
        $pageSize = $this->getPageSize();

        $result = $this->configService->getGroupList($tenantId, $params, $page, $pageSize);

        return $this->success(data: $result);
    }

    /**
     * 获取群组详情
     */
    #[GetMapping('/{id}')]
    public function detail(Request $request, int $id): Response
    {
        $tenantId = $request->user->tenant_id;

        $detail = $this->configService->getGroupDetail($tenantId, $id);

        if (!$detail) {
            return $this->error(ResultCode::NOT_FOUND, '群组不存在或无权限');
        }

        return $this->success(data: $detail);
    }

    /**
     * 获取群组统计数据
     */
    #[GetMapping('/{id}/statistics')]
    public function statistics(Request $request, int $id): Response
    {
        $tenantId = $request->user->tenant_id;

        $stats = $this->configService->getGroupStatistics($tenantId, $id);

        if (!$stats) {
            return $this->error(ResultCode::NOT_FOUND, '群组不存在或无权限');
        }

        return $this->success(data: $stats);
    }

    /**
     * 更新群组配置
     */
    #[PutMapping('/{id}')]
    public function update(Request $request, int $id): Response
    {
        $tenantId = $request->user->tenant_id;

        $validator = validate($request->all(), [
            'bet_amount' => 'sometimes|numeric|min:1',
            'platform_fee_rate' => 'sometimes|numeric|min:0|max:100',
            'telegram_admin_whitelist' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $data = $validator->validate();
        $result = $this->configService->updateConfig($tenantId, $id, $data);

        return $result['success']
            ? $this->success(message: $result['message'])
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 更新收款钱包地址（触发钱包变更流程）
     */
    #[PutMapping('/{id}/wallet')]
    public function updateWallet(Request $request, int $id): Response
    {
        $tenantId = $request->user->tenant_id;

        $validator = validate($request->all(), [
            'wallet_address' => 'required|string|size:34',
            'cooldown_minutes' => 'sometimes|integer|min:1|max:1440',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $data = $validator->validate();
        $cooldownMinutes = $data['cooldown_minutes'] ?? 10;

        $result = $this->configService->updateWalletAddress($tenantId, $id, $data['wallet_address'], $cooldownMinutes);

        return $result['success']
            ? $this->success(data: $result, message: '钱包变更已启动')
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 取消钱包变更
     */
    #[PostMapping('/{id}/cancel_wallet_change')]
    public function cancelWalletChange(Request $request, int $id): Response
    {
        $tenantId = $request->user->tenant_id;

        $result = $this->configService->cancelWalletChange($tenantId, $id);

        return $result['success']
            ? $this->success(message: $result['message'])
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 更新热钱包地址和私钥
     */
    #[PutMapping('/{id}/hot_wallet')]
    public function updateHotWallet(Request $request, int $id): Response
    {
        $tenantId = $request->user->tenant_id;

        $validator = validate($request->all(), [
            'hot_wallet_address' => 'required|string|size:34',
            'hot_wallet_private_key' => 'required|string|size:64',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $data = $validator->validate();

        $result = $this->configService->updateHotWallet(
            $tenantId,
            $id,
            $data['hot_wallet_address'],
            $data['hot_wallet_private_key']
        );

        return $result['success']
            ? $this->success(message: $result['message'])
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 更新群组状态（启用/停用）
     */
    #[PutMapping('/{id}/status')]
    public function updateStatus(Request $request, int $id): Response
    {
        $tenantId = $request->user->tenant_id;

        $validator = validate($request->all(), [
            'status' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $data = $validator->validate();

        $result = $this->configService->updateStatus($tenantId, $id, $data['status']);

        return $result['success']
            ? $this->success(message: $result['message'])
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }
}
