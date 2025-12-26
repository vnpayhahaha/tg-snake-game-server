<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TgGameGroupConfigLogService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏群组配置变更日志控制器
 */
#[RestController("/admin/tg_game")]
class TgGameGroupConfigLogController extends BasicController
{
    #[Inject]
    protected TgGameGroupConfigLogService $service;

    /**
     * 配置变更日志列表
     */
    #[GetMapping('/config_log/list')]
    #[Permission(code: 'tg_game:config_log:list')]
    #[OperationLog('配置变更日志列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: $this->service->getConfigLogPage(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }

    /**
     * 根据配置ID查询变更日志
     * 必须在 /config_log/{id} 之前
     */
    #[GetMapping('/config_log/by_config/{configId}')]
    #[Permission(code: 'tg_game:config_log:byConfig')]
    #[OperationLog('查看配置变更历史')]
    public function getByConfigId(Request $request, int $configId): Response
    {
        $limit = (int)$request->input('limit', 50);
        $logs = $this->service->getConfigHistory($configId, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 根据操作类型查询日志
     * 必须在 /config_log/{id} 之前
     */
    #[GetMapping('/config_log/by_action/{action}')]
    #[Permission(code: 'tg_game:config_log:byAction')]
    #[OperationLog('根据操作类型查询日志')]
    public function getByAction(Request $request, string $action): Response
    {
        $limit = (int)$request->input('limit', 100);
        $logs = $this->service->getLogsByAction($action, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 获取钱包变更历史
     * 必须在 /config_log/{id} 之前
     */
    #[GetMapping('/config_log/wallet_changes/{configId}')]
    #[Permission(code: 'tg_game:config_log:walletChanges')]
    #[OperationLog('查看钱包变更历史')]
    public function getWalletChanges(Request $request, int $configId): Response
    {
        $logs = $this->service->getWalletChangeHistory($configId);
        return $this->success(data: $logs);
    }

    /**
     * 导出配置变更日志
     * 必须在 /config_log/{id} 之前
     */
    #[GetMapping('/config_log/export')]
    #[Permission(code: 'tg_game:config_log:export')]
    #[OperationLog('导出配置变更日志')]
    public function export(Request $request): Response
    {
        $params = $request->all();
        $records = $this->service->getConfigLogExportData($params, 10000);

        if ($records->isEmpty()) {
            return $this->error(ResultCode::BAD_REQUEST, '没有可导出的数据');
        }

        $exportData = [['日志ID', '配置ID', '群组名称', '操作类型', '操作来源', '变更前数据', '变更后数据', '操作人ID', '创建时间']];

        foreach ($records as $record) {
            $exportData[] = [
                $record->id,
                $record->config_id,
                $record->group_name ?? '',
                $record->action,
                $record->source == 1 ? '管理后台' : ($record->source == 2 ? 'Telegram Bot' : '系统'),
                $record->old_data ?? '',
                $record->new_data ?? '',
                $record->operator_id ?? '',
                (string)$record->created_at,
            ];
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($exportData, null, 'A1');

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            $sheet->getStyle('A1:I1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'config_change_logs_' . date('YmdHis') . '.xlsx';
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
    #[GetMapping('/config_log/{id}')]
    #[Permission(code: 'tg_game:config_log:detail')]
    #[OperationLog('查看日志详情')]
    public function detail(Request $request, int $id): Response
    {
        $log = $this->service->getConfigLogById($id);
        if (!$log) {
            return $this->error(ResultCode::NOT_FOUND, '日志不存在');
        }
        return $this->success(data: $log);
    }
}
