<?php

namespace app\service;

use app\repository\TelegramCommandMessageRecordRepository;
use DI\Attribute\Inject;
use Illuminate\Support\Collection;
use support\Db;
use support\Log;

/**
 * Telegram命令消息记录服务
 * @extends BaseService
 */
class TelegramCommandMessageRecordService extends BaseService
{
    #[Inject]
    public TelegramCommandMessageRecordRepository $repository;

    /**
     * 分页获取命令消息
     */
    public function getCommandMessagePage(array $params, int $page = 1, int $pageSize = 10): array
    {
        return $this->page($params, $page, $pageSize);
    }

    /**
     * 根据群组ID获取命令消息
     */
    public function getCommandMessagesByGroup(int $groupId, int $limit = 100): Collection
    {
        $params = ['group_id' => $groupId];
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 根据TG用户ID获取命令消息
     */
    public function getCommandMessagesByUser(int $tgUserId, int $limit = 50): Collection
    {
        $params = ['tg_user_id' => $tgUserId];
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 根据命令类型获取消息
     */
    public function getCommandMessagesByCommand(string $command, int $limit = 100): Collection
    {
        $params = ['command' => $command];
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 获取命令消息统计
     */
    public function getCommandMessageStatistics(int $groupId = null, string $dateStart = null, string $dateEnd = null): array
    {
        $query = Db::table('telegram_command_message_record');

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        if ($dateStart) {
            $query->where('created_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->where('created_at', '<=', $dateEnd);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'success' => (clone $query)->where('is_success', 1)->count(),
            'failed' => (clone $query)->where('is_success', 0)->count(),
        ];

        // 按命令类型统计
        $commandStats = (clone $query)
            ->select('command', Db::raw('COUNT(*) as count'))
            ->groupBy('command')
            ->get()
            ->pluck('count', 'command')
            ->toArray();

        $stats['by_command'] = $commandStats;

        return $stats;
    }

    /**
     * 获取当日命令消息统计
     */
    public function getDailyCommandMessageStatistics(int $groupId = null, string $date = null): array
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $dateStart = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';

        return $this->getCommandMessageStatistics($groupId, $dateStart, $dateEnd);
    }

    /**
     * 获取导出数据
     */
    public function getCommandMessageExportData(array $params, int $limit = 10000): Collection
    {
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 获取命令消息列表
     */
    public function getCommandMessageList(array $params): Collection
    {
        return $this->repository->list($params);
    }

    /**
     * 根据ID获取命令消息
     */
    public function getCommandMessageById(int $id)
    {
        return $this->repository->findById($id);
    }

    /**
     * 删除命令消息（软删除）
     */
    public function deleteCommandMessage($ids): int
    {
        return $this->deleteById($ids);
    }

    /**
     * 真实删除命令消息
     */
    public function realDeleteCommandMessage(array $ids): bool
    {
        return $this->realDelete($ids);
    }

    /**
     * 创建命令消息记录
     */
    public function createCommandMessage(array $data)
    {
        return $this->repository->create([
            'group_id' => $data['group_id'] ?? null,
            'group_name' => $data['group_name'] ?? null,
            'tg_chat_id' => $data['tg_chat_id'],
            'tg_user_id' => $data['tg_user_id'],
            'tg_username' => $data['tg_username'] ?? null,
            'command' => $data['command'],
            'message_text' => $data['message_text'] ?? null,
            'response_text' => $data['response_text'] ?? null,
            'is_success' => $data['is_success'] ?? false,
            'error_message' => $data['error_message'] ?? null,
        ]);
    }

    /**
     * 更新命令消息记录响应
     */
    public function updateCommandMessageResponse(int $id, string $responseText, bool $isSuccess, string $errorMessage = null): bool
    {
        try {
            return $this->repository->updateById($id, [
                'response_text' => $responseText,
                'is_success' => $isSuccess,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            Log::error('更新命令消息响应失败: ' . $e->getMessage());
            return false;
        }
    }
}
