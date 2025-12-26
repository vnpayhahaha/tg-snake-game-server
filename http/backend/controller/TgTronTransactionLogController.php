<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\TgTronMonitorService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏TRON交易日志管理控制器
 */
#[RestController("/admin/tg_game")]
class TgTronTransactionLogController extends BasicController
{
    #[Inject]
    protected TgTronMonitorService $service;

    /**
     * TRON交易日志列表
     */
    #[GetMapping('/tron_log/list')]
    #[Permission(code: 'tg_game:tron_log:list')]
    #[OperationLog('TRON交易日志列表')]
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
     * 获取未处理的交易日志
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/unprocessed')]
    #[Permission(code: 'tg_game:tron_log:unprocessed')]
    #[OperationLog('查看未处理交易')]
    public function getUnprocessed(Request $request): Response
    {
        $logs = $this->service->getUnprocessedLogs();
        return $this->success(data: $logs);
    }

    /**
     * 获取无效交易日志
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/invalid')]
    #[Permission(code: 'tg_game:tron_log:invalid')]
    #[OperationLog('查看无效交易')]
    public function getInvalid(Request $request): Response
    {
        $logs = $this->service->getInvalidLogs();
        return $this->success(data: $logs);
    }

    /**
     * 根据群组ID查询交易日志
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/by_group/{groupId}')]
    #[Permission(code: 'tg_game:tron_log:byGroup')]
    #[OperationLog('根据群组查询交易日志')]
    public function getByGroupId(Request $request, int $groupId): Response
    {
        $limit = (int)$request->input('limit', 100);
        $logs = $this->service->getByGroupId($groupId, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 根据交易哈希查询
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/by_tx_hash/{txHash}')]
    #[Permission(code: 'tg_game:tron_log:byTxHash')]
    #[OperationLog('根据交易哈希查询')]
    public function getByTxHash(Request $request, string $txHash): Response
    {
        $log = $this->service->getByTxHash($txHash);
        if (!$log) {
            return $this->error(ResultCode::NOT_FOUND, '交易日志不存在');
        }
        return $this->success(data: $log);
    }

    /**
     * 根据钱包地址查询交易日志
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/by_address/{address}')]
    #[Permission(code: 'tg_game:tron_log:byAddress')]
    #[OperationLog('根据钱包地址查询交易')]
    public function getByAddress(Request $request, string $address): Response
    {
        $direction = $request->input('direction'); // from/to
        $limit = (int)$request->input('limit', 50);
        $logs = $this->service->getByAddress($address, $direction, $limit);
        return $this->success(data: $logs);
    }

    /**
     * 获取交易统计
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/statistics')]
    #[Permission(code: 'tg_game:tron_log:statistics')]
    #[OperationLog('查看交易统计')]
    public function getStatistics(Request $request): Response
    {
        $groupId = $request->input('group_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $stats = $this->service->getTransactionStatistics($groupId, $dateStart, $dateEnd);
        return $this->success(data: $stats);
    }

    /**
     * 获取当日交易统计
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/daily_statistics')]
    #[Permission(code: 'tg_game:tron_log:dailyStatistics')]
    #[OperationLog('查看当日交易统计')]
    public function getDailyStatistics(Request $request): Response
    {
        $groupId = $request->input('group_id');
        $date = $request->input('date', date('Y-m-d'));
        $stats = $this->service->getDailyStatistics($groupId, $date);
        return $this->success(data: $stats);
    }

    /**
     * 导出交易日志
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/export')]
    #[Permission(code: 'tg_game:tron_log:export')]
    #[OperationLog('导出交易日志')]
    public function export(Request $request): Response
    {
        $params = $request->all();

        // 获取导出数据（不分页，最多导出10000条）
        $records = $this->service->getExportData($params, 10000);

        if ($records->isEmpty()) {
            return $this->error(ResultCode::BAD_REQUEST, '没有可导出的数据');
        }

        // 准备导出数据
        $exportData = [];
        $exportData[] = [
            '日志ID',
            '群组ID',
            '群组名称',
            '交易哈希',
            '发送地址',
            '接收地址',
            '金额(TRX)',
            '区块高度',
            '区块时间戳',
            '交易状态',
            '是否有效',
            '无效原因',
            '是否已处理',
            '创建时间',
        ];

        foreach ($records as $record) {
            $exportData[] = [
                $record->id,
                $record->group_id,
                $record->group_name ?? '',
                $record->tx_hash,
                $record->from_address,
                $record->to_address,
                $record->amount,
                $record->block_height,
                $record->block_timestamp,
                $record->status,
                $record->is_valid ? '是' : '否',
                $record->invalid_reason ?? '',
                $record->processed ? '是' : '否',
                (string)$record->created_at,
            ];
        }

        // 使用PhpSpreadsheet导出Excel
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($exportData, null, 'A1');

            // 设置列宽自动调整
            foreach (range('A', 'N') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 设置标题行样式
            $sheet->getStyle('A1:N1')->getFont()->setBold(true);
            $sheet->getStyle('A1:N1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $fileName = 'tron_transaction_logs_' . date('YmdHis') . '.xlsx';
            $tempFile = sys_get_temp_dir() . '/' . $fileName;

            $writer->save($tempFile);

            $response = response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

            // 注册关闭时删除临时文件
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
     * 必须在 /tron_log/{id} 之前
     */
    #[GetMapping('/tron_log/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'group_id',
            'tx_hash',
            'from_address',
            'to_address',
            'amount',
            'status',
            'is_valid',
            'created_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 获取交易日志详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/tron_log/{id}')]
    #[Permission(code: 'tg_game:tron_log:detail')]
    #[OperationLog('查看交易日志详情')]
    public function detail(Request $request, int $id): Response
    {
        $log = $this->service->findById($id);
        if (!$log) {
            return $this->error(ResultCode::NOT_FOUND, '交易日志不存在');
        }
        return $this->success(data: $log);
    }

    /**
     * 手动重新处理交易
     */
    #[PostMapping('/tron_log/{id}/reprocess')]
    #[Permission(code: 'tg_game:tron_log:reprocess')]
    #[OperationLog('重新处理交易')]
    public function reprocessTransaction(Request $request, int $id): Response
    {
        $result = $this->service->reprocessTransaction($id);
        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 手动同步区块链交易（管理员操作）
     */
    #[PostMapping('/tron_log/sync_transactions')]
    #[Permission(code: 'tg_game:tron_log:syncTransactions')]
    #[OperationLog('手动同步区块链交易')]
    public function syncTransactions(Request $request): Response
    {
        $groupId = $request->input('group_id');
        $startBlock = $request->input('start_block');
        $endBlock = $request->input('end_block');

        $result = $this->service->syncTransactions($groupId, $startBlock, $endBlock);
        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }
}
