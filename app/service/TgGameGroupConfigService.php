<?php

namespace app\service;

use app\constants\TgGameGroupConfig as ConfigConst;
use app\repository\TgGameGroupConfigRepository;
use app\repository\TgGameGroupConfigLogRepository;
use app\repository\TgSnakeNodeRepository;
use DI\Attribute\Inject;
use Carbon\Carbon;
use support\Db;
use support\Log;

/**
 * 游戏群组配置服务
 * @extends BaseService
 */
class TgGameGroupConfigService extends BaseService
{
    #[Inject]
    public TgGameGroupConfigRepository $repository;

    #[Inject]
    protected TgGameGroupConfigLogRepository $configLogRepository;

    #[Inject]
    protected TgSnakeNodeRepository $snakeNodeRepository;

    /**
     * 根据Telegram群组ID获取配置
     */
    public function getByTgChatId(int $tgChatId)
    {
        return $this->repository->getByTgChatId($tgChatId);
    }

    /**
     * 根据租户ID获取配置列表
     */
    public function getByTenantId(string $tenantId)
    {
        return $this->repository->getByTenantId($tenantId);
    }

    /**
     * 获取所有活跃配置
     */
    public function getActiveConfigs()
    {
        return $this->repository->getActiveConfigs();
    }

    /**
     * 检查钱包变更状态
     */
    public function checkWalletChangeStatus(int $id): int
    {
        return $this->repository->checkWalletChangeStatus($id);
    }

    /**
     * 开始钱包变更流程
     */
    public function startWalletChange(int $id, string $newAddress, int $cooldownMinutes = 10): array
    {
        try {
            Db::beginTransaction();

            $config = $this->repository->findById($id);
            if (!$config) {
                throw new \Exception('配置不存在');
            }

            // 检查当前状态
            if ($config->wallet_change_status != ConfigConst::WALLET_CHANGE_STATUS_NORMAL) {
                throw new \Exception('钱包变更中，无法再次发起');
            }

            // 检查新地址是否与旧地址相同
            if ($config->wallet_address === $newAddress) {
                throw new \Exception('新钱包地址与当前地址相同');
            }

            $startAt = Carbon::now();
            $endAt = $startAt->copy()->addMinutes($cooldownMinutes);

            // 更新配置
            $success = $this->repository->startWalletChange($id, $newAddress, $startAt, $endAt);
            if (!$success) {
                throw new \Exception('开始钱包变更失败');
            }

            // 记录配置变更日志
            $this->logConfigChange($config, [
                'pending_wallet_address' => $newAddress,
                'wallet_change_status' => 2,
                'wallet_change_start_at' => $startAt,
                'wallet_change_end_at' => $endAt,
            ], 2); // 来源：TG群指令

            Db::commit();

            return [
                'success' => true,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'cooldown_minutes' => $cooldownMinutes,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('开始钱包变更失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 取消钱包变更
     */
    public function cancelWalletChange(int $id): array
    {
        try {
            Db::beginTransaction();

            $config = $this->repository->findById($id);
            if (!$config) {
                throw new \Exception('配置不存在');
            }

            // 检查当前状态
            if ($config->wallet_change_status != ConfigConst::WALLET_CHANGE_STATUS_CHANGING) {
                throw new \Exception('当前没有进行中的钱包变更');
            }

            // 取消变更
            $success = $this->repository->cancelWalletChange($id);
            if (!$success) {
                throw new \Exception('取消钱包变更失败');
            }

            // 记录日志
            $this->logConfigChange($config, [
                'pending_wallet_address' => null,
                'wallet_change_status' => 1,
                'wallet_change_start_at' => null,
                'wallet_change_end_at' => null,
            ], 2);

            Db::commit();

            return [
                'success' => true,
                'message' => '钱包变更已取消',
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('取消钱包变更失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 完成钱包变更（冷却期结束后）
     */
    public function completeWalletChange(int $id): array
    {
        try {
            Db::beginTransaction();

            $config = $this->repository->findById($id);
            if (!$config) {
                throw new \Exception('配置不存在');
            }

            // 检查状态
            if ($config->wallet_change_status != ConfigConst::WALLET_CHANGE_STATUS_CHANGING) {
                throw new \Exception('没有进行中的钱包变更');
            }

            // 检查是否到达结束时间
            if (Carbon::now()->lt($config->wallet_change_end_at)) {
                throw new \Exception('冷却期未结束，无法完成变更');
            }

            // 归档旧钱包周期的节点
            $oldWalletCycle = $config->wallet_change_count;
            $archivedCount = $this->snakeNodeRepository->archiveNodes(
                $config->id,
                $oldWalletCycle
            );

            Log::info("归档节点数量: {$archivedCount}, 群组: {$config->id}, 钱包周期: {$oldWalletCycle}");

            // 完成变更
            $newWalletCycle = $oldWalletCycle + 1;
            $success = $this->repository->completeWalletChange(
                $id,
                $config->pending_wallet_address,
                $newWalletCycle
            );

            if (!$success) {
                throw new \Exception('完成钱包变更失败');
            }

            // 记录日志
            $this->logConfigChange($config, [
                'wallet_address' => $config->pending_wallet_address,
                'wallet_change_count' => $newWalletCycle,
                'pending_wallet_address' => null,
                'wallet_change_status' => 1,
                'wallet_change_start_at' => null,
                'wallet_change_end_at' => null,
            ], 2);

            Db::commit();

            return [
                'success' => true,
                'message' => '钱包变更完成',
                'new_address' => $config->pending_wallet_address,
                'new_wallet_cycle' => $newWalletCycle,
                'archived_nodes' => $archivedCount,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('完成钱包变更失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 更新配置
     */
    public function updateConfig(int $id, array $data, int $changeSource = 1): bool
    {
        try {
            Db::beginTransaction();

            $config = $this->repository->findById($id);
            if (!$config) {
                throw new \Exception('配置不存在');
            }

            // 更新配置
            $success = $this->repository->updateById($id, $data);
            if (!$success) {
                throw new \Exception('更新配置失败');
            }

            // 记录日志
            $this->logConfigChange($config, $data, $changeSource);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('更新配置失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 记录配置变更日志
     */
    protected function logConfigChange($oldConfig, array $newData, int $changeSource = 1): void
    {
        try {
            $this->configLogRepository->logConfigChange([
                'config_id' => $oldConfig->id,
                'tg_chat_id' => $oldConfig->tg_chat_id,
                'change_params' => json_encode($newData),
                'old_config' => json_encode($oldConfig->toArray()),
                'new_config' => json_encode(array_merge($oldConfig->toArray(), $newData)),
                'operator' => $this->getCurrentUserName() ?: 'system',
                'operator_ip' => $this->getOperatorIp(),
                'change_source' => $changeSource,
                'tg_message_id' => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning("记录配置变更日志失败: " . $e->getMessage(), [
                'config_id' => $oldConfig->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 获取操作者IP（兼容多种环境）
     */
    protected function getOperatorIp(): string
    {
        try {
            $request = request();
            if ($request) {
                return $request->getRealIp() ?? '127.0.0.1';
            }
        } catch (\Throwable $e) {
            // 忽略错误
        }
        return '127.0.0.1';
    }

    /**
     * 获取配置变更历史
     */
    public function getConfigHistory(int $configId, int $limit = 20)
    {
        return $this->configLogRepository->getConfigHistory($configId, $limit);
    }
}
