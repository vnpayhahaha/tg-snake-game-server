<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TgPlayerWalletBindingLogService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram玩家钱包绑定日志控制器
 */
#[RestController("/admin/tg_game")]
class TgPlayerWalletBindingLogController extends BasicController
{
    #[Inject]
    protected TgPlayerWalletBindingLogService $service;

    /**
     * 钱包绑定日志列表
     */
    #[GetMapping('/wallet_binding_log/list')]
    #[Permission(code: 'tg_game:wallet_binding_log:list')]
    #[OperationLog('钱包绑定日志列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: $this->service->getBindingLogPage(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }

    /**
     * 根据群组和TG用户查询绑定日志
     * 必须在 /wallet_binding_log/{id} 之前
     */
    #[GetMapping('/wallet_binding_log/by_user/{groupId}/{tgUserId}')]
    #[Permission(code: 'tg_game:wallet_binding_log:byUser')]
    #[OperationLog('查看用户绑定历史')]
    public function getByUser(Request $request, int $groupId, int $tgUserId): Response
    {
        $limit = (int)$request->input('limit', 50);
        $logs = $this->service->getBindingHistory($groupId, $tgUserId, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 根据群组查询绑定日志
     * 必须在 /wallet_binding_log/{id} 之前
     */
    #[GetMapping('/wallet_binding_log/by_group/{groupId}')]
    #[Permission(code: 'tg_game:wallet_binding_log:byGroup')]
    #[OperationLog('查看群组绑定日志')]
    public function getByGroup(Request $request, int $groupId): Response
    {
        $limit = (int)$request->input('limit', 100);
        $logs = $this->service->getGroupBindingLogs($groupId, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 根据钱包地址查询绑定日志
     * 必须在 /wallet_binding_log/{id} 之前
     */
    #[GetMapping('/wallet_binding_log/by_wallet/{walletAddress}')]
    #[Permission(code: 'tg_game:wallet_binding_log:byWallet')]
    #[OperationLog('根据钱包地址查询日志')]
    public function getByWallet(Request $request, string $walletAddress): Response
    {
        $logs = $this->service->getLogsByWalletAddress($walletAddress);
        return $this->success(data: $logs);
    }

    /**
     * 根据操作类型查询日志
     * 必须在 /wallet_binding_log/{id} 之前
     */
    #[GetMapping('/wallet_binding_log/by_action/{action}')]
    #[Permission(code: 'tg_game:wallet_binding_log:byAction')]
    #[OperationLog('根据操作类型查询日志')]
    public function getByAction(Request $request, string $action): Response
    {
        $limit = (int)$request->input('limit', 100);
        $logs = $this->service->getLogsByAction($action, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 导出绑定日志
     * 必须在 /wallet_binding_log/{id} 之前
     */
    #[GetMapping('/wallet_binding_log/export')]
    #[Permission(code: 'tg_game:wallet_binding_log:export')]
    #[OperationLog('导出绑定日志')]
    public function export(Request $request): Response
    {
        $params = $request->all();
        $records = $this->service->getBindingLogExportData($params, 10000);

        if ($records->isEmpty()) {
            return $this->error(ResultCode::BAD_REQUEST, '没有可导出的数据');
        }

        $exportData = [['日志ID', '群组ID', '群组名称', 'TG用户ID', 'TG用户名', '操作类型', '旧钱包地址', '新钱包地址', '操作来源', '创建时间']];

        foreach ($records as $record) {
            $sourceText = match($record->source) {
                1 => '管理后台',
                2 => 'Telegram Bot',
                default => '系统'
            };

            $exportData[] = [
                $record->id,
                $record->group_id,
                $record->group_name ?? '',
                $record->tg_user_id,
                $record->tg_username ?? '',
                $record->action,
                $record->old_wallet_address ?? '',
                $record->new_wallet_address ?? '',
                $sourceText,
                (string)$record->created_at,
            ];
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($exportData, null, 'A1');

            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->getStyle('A1:J1')->getFont()->setBold(true);
            $sheet->getStyle('A1:J1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'wallet_binding_logs_' . date('YmdHis') . '.xlsx';
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
     * 获取日志详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/wallet_binding_log/{id}')]
    #[Permission(code: 'tg_game:wallet_binding_log:detail')]
    #[OperationLog('查看日志详情')]
    public function detail(Request $request, int $id): Response
    {
        $log = $this->service->getBindingLogById($id);
        if (!$log) {
            return $this->error(ResultCode::NOT_FOUND, '日志不存在');
        }
        return $this->success(data: $log);
    }
}
