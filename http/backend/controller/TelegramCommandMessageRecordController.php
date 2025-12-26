<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TelegramCommandMessageRecordService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram命令消息记录控制器
 */
#[RestController("/admin/tg_game")]
class TelegramCommandMessageRecordController extends BasicController
{
    #[Inject]
    protected TelegramCommandMessageRecordService $service;

    /**
     * Telegram命令消息记录列表
     */
    #[GetMapping('/command_message/list')]
    #[Permission(code: 'tg_game:command_message:list')]
    #[OperationLog('命令消息记录列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: $this->service->getCommandMessagePage(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }

    /**
     * 根据群组ID查询命令消息
     * 必须在 /command_message/{id} 之前
     */
    #[GetMapping('/command_message/by_group/{groupId}')]
    #[Permission(code: 'tg_game:command_message:byGroup')]
    #[OperationLog('根据群组查询命令消息')]
    public function getByGroupId(Request $request, int $groupId): Response
    {
        $limit = (int)$request->input('limit', 100);
        $messages = $this->service->getCommandMessagesByGroup($groupId, $limit);
        return $this->success(data: $messages);
    }

    /**
     * 根据TG用户ID查询命令消息
     * 必须在 /command_message/{id} 之前
     */
    #[GetMapping('/command_message/by_user/{tgUserId}')]
    #[Permission(code: 'tg_game:command_message:byUser')]
    #[OperationLog('根据用户查询命令消息')]
    public function getByUserId(Request $request, int $tgUserId): Response
    {
        $limit = (int)$request->input('limit', 50);
        $messages = $this->service->getCommandMessagesByUser($tgUserId, $limit);
        return $this->success(data: $messages);
    }

    /**
     * 根据命令类型查询消息
     * 必须在 /command_message/{id} 之前
     */
    #[GetMapping('/command_message/by_command/{command}')]
    #[Permission(code: 'tg_game:command_message:byCommand')]
    #[OperationLog('根据命令类型查询消息')]
    public function getByCommand(Request $request, string $command): Response
    {
        $limit = (int)$request->input('limit', 100);
        $messages = $this->service->getCommandMessagesByCommand($command, $limit);
        return $this->success(data: $messages);
    }

    /**
     * 获取命令消息统计
     * 必须在 /command_message/{id} 之前
     */
    #[GetMapping('/command_message/statistics')]
    #[Permission(code: 'tg_game:command_message:statistics')]
    #[OperationLog('查看命令消息统计')]
    public function getStatistics(Request $request): Response
    {
        $groupId = $request->input('group_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $stats = $this->service->getCommandMessageStatistics($groupId, $dateStart, $dateEnd);
        return $this->success(data: $stats);
    }

    /**
     * 获取当日命令消息统计
     * 必须在 /command_message/{id} 之前
     */
    #[GetMapping('/command_message/daily_statistics')]
    #[Permission(code: 'tg_game:command_message:dailyStatistics')]
    #[OperationLog('查看当日命令消息统计')]
    public function getDailyStatistics(Request $request): Response
    {
        $groupId = $request->input('group_id');
        $date = $request->input('date', date('Y-m-d'));
        $stats = $this->service->getDailyCommandMessageStatistics($groupId, $date);
        return $this->success(data: $stats);
    }

    /**
     * 导出命令消息记录
     * 必须在 /command_message/{id} 之前
     */
    #[GetMapping('/command_message/export')]
    #[Permission(code: 'tg_game:command_message:export')]
    #[OperationLog('导出命令消息记录')]
    public function export(Request $request): Response
    {
        $params = $request->all();
        $records = $this->service->getCommandMessageExportData($params, 10000);

        if ($records->isEmpty()) {
            return $this->error(ResultCode::BAD_REQUEST, '没有可导出的数据');
        }

        $exportData = [['记录ID', '群组ID', '群组名称', 'TG聊天ID', 'TG用户ID', 'TG用户名', '命令', '消息内容', '响应内容', '是否成功', '错误信息', '创建时间']];

        foreach ($records as $record) {
            $exportData[] = [
                $record->id,
                $record->group_id ?? '',
                $record->group_name ?? '',
                $record->tg_chat_id,
                $record->tg_user_id,
                $record->tg_username ?? '',
                $record->command,
                $record->message_text ?? '',
                $record->response_text ?? '',
                $record->is_success ? '是' : '否',
                $record->error_message ?? '',
                (string)$record->created_at,
            ];
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($exportData, null, 'A1');

            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->getStyle('A1:L1')->getFont()->setBold(true);
            $sheet->getStyle('A1:L1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'telegram_command_messages_' . date('YmdHis') . '.xlsx';
            $tempFile = sys_get_temp_dir() . '/' . $fileName;
            $writer->save($tempFile);

            $response = response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

            register_shutdown_function(function () use ($tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            });

            return $response;
        } catch (\Throwable $e) {
            return $this->error(ResultCode::FAIL, '导出失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取远程搜索数据
     * 必须在 /command_message/{id} 之前
     */
    #[GetMapping('/command_message/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'group_id',
            'tg_chat_id',
            'tg_user_id',
            'tg_username',
            'command',
            'is_success',
            'created_at',
        ];
        return $this->success(
            $this->service->getCommandMessageList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 获取命令消息详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/command_message/{id}')]
    #[Permission(code: 'tg_game:command_message:detail')]
    #[OperationLog('查看命令消息详情')]
    public function detail(Request $request, int $id): Response
    {
        $message = $this->service->getCommandMessageById($id);
        if (!$message) {
            return $this->error(ResultCode::NOT_FOUND, '命令消息不存在');
        }
        return $this->success(data: $message);
    }

    /**
     * 删除命令消息（软删除）
     */
    #[DeleteMapping('/command_message')]
    #[Permission(code: 'tg_game:command_message:delete')]
    #[OperationLog('删除命令消息')]
    public function delete(Request $request): Response
    {
        $this->service->deleteCommandMessage($request->all());
        return $this->success();
    }

    /**
     * 真实删除命令消息
     */
    #[DeleteMapping('/command_message/real_delete')]
    #[Permission(code: 'tg_game:command_message:realDelete')]
    #[OperationLog('真实删除命令消息')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDeleteCommandMessage((array)$request->all()) ? $this->success() : $this->error();
    }
}
