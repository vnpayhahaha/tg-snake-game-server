<?php

namespace http\tenant\Service;

use app\lib\helper\CryptoHelper;
use app\repository\TgGameGroupConfigRepository;
use app\repository\TgGameGroupRepository;
use app\service\TgGameGroupConfigService as BaseTgGameGroupConfigService;
use DI\Attribute\Inject;
use support\Log;

/**
 * 租户端TG游戏群组配置管理服务
 */
class TgGameGroupConfigService
{
    #[Inject]
    protected TgGameGroupConfigRepository $configRepository;

    #[Inject]
    protected TgGameGroupRepository $groupRepository;

    #[Inject]
    protected BaseTgGameGroupConfigService $baseConfigService;

    /**
     * 获取租户下的群组配置列表（分页）
     */
    public function getGroupList(string $tenantId, array $params = [], int $page = 1, int $pageSize = 10): array
    {
        return $this->configRepository->getByTenantIdPaginated($tenantId, $params, $page, $pageSize);
    }

    /**
     * 获取群组配置详情
     */
    public function getGroupDetail(string $tenantId, int $configId): ?array
    {
        // 验证归属
        if (!$this->configRepository->belongsToTenant($configId, $tenantId)) {
            return null;
        }

        $config = $this->configRepository->findById($configId);
        if (!$config) {
            return null;
        }

        // 加载关联的游戏群组
        $config->load('group');

        // 脱敏处理私钥
        $data = $config->toArray();
        if (!empty($data['hot_wallet_private_key'])) {
            $data['hot_wallet_private_key'] = $this->maskPrivateKey($data['hot_wallet_private_key']);
        }

        return $data;
    }

    /**
     * 更新群组配置（通用字段）
     */
    public function updateConfig(string $tenantId, int $configId, array $data): array
    {
        // 验证归属
        if (!$this->configRepository->belongsToTenant($configId, $tenantId)) {
            return ['success' => false, 'message' => '群组不存在或无权限'];
        }

        // 允许更新的字段
        $allowedFields = [
            'bet_amount',
            'platform_fee_rate',
            'telegram_admin_whitelist',
        ];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            return ['success' => false, 'message' => '没有可更新的字段'];
        }

        try {
            $success = $this->baseConfigService->updateConfig($configId, $updateData, 1);
            return $success
                ? ['success' => true, 'message' => '更新成功']
                : ['success' => false, 'message' => '更新失败'];
        } catch (\Exception $e) {
            Log::error('更新群组配置失败: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 更新收款钱包地址（触发钱包变更流程）
     */
    public function updateWalletAddress(string $tenantId, int $configId, string $newAddress, int $cooldownMinutes = 10): array
    {
        // 验证归属
        if (!$this->configRepository->belongsToTenant($configId, $tenantId)) {
            return ['success' => false, 'message' => '群组不存在或无权限'];
        }

        // 验证钱包地址格式（TRON地址以T开头，34位）
        if (!preg_match('/^T[a-zA-Z0-9]{33}$/', $newAddress)) {
            return ['success' => false, 'message' => '无效的TRON钱包地址格式'];
        }

        return $this->baseConfigService->startWalletChange($configId, $newAddress, $cooldownMinutes);
    }

    /**
     * 取消钱包变更
     */
    public function cancelWalletChange(string $tenantId, int $configId): array
    {
        // 验证归属
        if (!$this->configRepository->belongsToTenant($configId, $tenantId)) {
            return ['success' => false, 'message' => '群组不存在或无权限'];
        }

        return $this->baseConfigService->cancelWalletChange($configId);
    }

    /**
     * 更新热钱包地址和私钥
     */
    public function updateHotWallet(string $tenantId, int $configId, string $hotWalletAddress, string $hotWalletPrivateKey): array
    {
        // 验证归属
        if (!$this->configRepository->belongsToTenant($configId, $tenantId)) {
            return ['success' => false, 'message' => '群组不存在或无权限'];
        }

        // 验证钱包地址格式
        if (!preg_match('/^T[a-zA-Z0-9]{33}$/', $hotWalletAddress)) {
            return ['success' => false, 'message' => '无效的TRON钱包地址格式'];
        }

        // 验证私钥格式（64位十六进制）
        if (!preg_match('/^[a-fA-F0-9]{64}$/', $hotWalletPrivateKey)) {
            return ['success' => false, 'message' => '无效的私钥格式'];
        }

        try {
            // 使用AES-256-CBC加密私钥
            $encryptedPrivateKey = CryptoHelper::encrypt($hotWalletPrivateKey);

            $success = $this->configRepository->updateById($configId, [
                'hot_wallet_address' => $hotWalletAddress,
                'hot_wallet_private_key' => $encryptedPrivateKey,
            ]);

            return $success
                ? ['success' => true, 'message' => '热钱包更新成功']
                : ['success' => false, 'message' => '热钱包更新失败'];
        } catch (\Exception $e) {
            Log::error('更新热钱包失败: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 更新群组状态（启用/停用）
     */
    public function updateStatus(string $tenantId, int $configId, int $status): array
    {
        // 验证归属
        if (!$this->configRepository->belongsToTenant($configId, $tenantId)) {
            return ['success' => false, 'message' => '群组不存在或无权限'];
        }

        // 验证状态值
        if (!in_array($status, [0, 1], true)) {
            return ['success' => false, 'message' => '无效的状态值'];
        }

        try {
            $success = $this->configRepository->updateById($configId, ['status' => $status]);
            $statusText = $status === 1 ? '启用' : '停用';

            return $success
                ? ['success' => true, 'message' => "群组已{$statusText}"]
                : ['success' => false, 'message' => '状态更新失败'];
        } catch (\Exception $e) {
            Log::error('更新群组状态失败: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取群组统计数据
     */
    public function getGroupStatistics(string $tenantId, int $configId): ?array
    {
        // 验证归属
        if (!$this->configRepository->belongsToTenant($configId, $tenantId)) {
            return null;
        }

        $config = $this->configRepository->findById($configId);
        if (!$config || !$config->group) {
            return null;
        }

        $groupId = $config->group->id;

        // 获取节点统计
        $nodeRepository = \support\Container::get(\app\repository\TgSnakeNodeRepository::class);
        $nodeStats = $nodeRepository->getGroupStatistics($groupId);

        // 获取中奖统计
        $prizeRepository = \support\Container::get(\app\repository\TgPrizeRecordRepository::class);
        $prizeStats = $prizeRepository->getGroupStatistics($groupId);

        return [
            'group_id' => $groupId,
            'config_id' => $configId,
            'tg_chat_title' => $config->tg_chat_title,
            'node_stats' => $nodeStats,
            'prize_stats' => $prizeStats,
            'prize_pool_amount' => $config->group->prize_pool_amount ?? 0,
        ];
    }

    /**
     * 私钥脱敏处理
     */
    protected function maskPrivateKey(string $privateKey): string
    {
        if (strlen($privateKey) <= 8) {
            return '****';
        }
        return substr($privateKey, 0, 4) . '****' . substr($privateKey, -4);
    }
}
