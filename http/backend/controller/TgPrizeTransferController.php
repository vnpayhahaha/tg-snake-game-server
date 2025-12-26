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
use app\service\TgPrizeTransferService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏中奖转账记录管理控制器
 */
#[RestController("/admin/tg_game")]
class TgPrizeTransferController extends BasicController
{
    #[Inject]
    protected TgPrizeTransferService $service;

    /**
     * 中奖转账记录列表
     */
    #[GetMapping('/prize_transfer/list')]
    #[Permission(code: 'tg_game:prize_transfer:list')]
    #[OperationLog('中奖转账记录列表')]
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
     * 获取待处理的转账
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/pending')]
    #[Permission(code: 'tg_game:prize_transfer:pending')]
    #[OperationLog('查看待处理转账')]
    public function getPendingTransfers(Request $request): Response
    {
        $transfers = $this->service->getPendingTransfers();
        return $this->success(data: $transfers);
    }

    /**
     * 获取处理中的转账
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/processing')]
    #[Permission(code: 'tg_game:prize_transfer:processing')]
    #[OperationLog('查看处理中转账')]
    public function getProcessingTransfers(Request $request): Response
    {
        $transfers = $this->service->getProcessingTransfers();
        return $this->success(data: $transfers);
    }

    /**
     * 获取失败的转账
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/failed')]
    #[Permission(code: 'tg_game:prize_transfer:failed')]
    #[OperationLog('查看失败转账')]
    public function getFailedTransfers(Request $request): Response
    {
        $transfers = $this->service->getFailedTransfers();
        return $this->success(data: $transfers);
    }

    /**
     * 根据中奖记录ID查询转账
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/by_prize/{prizeId}')]
    #[Permission(code: 'tg_game:prize_transfer:byPrize')]
    #[OperationLog('根据中奖记录查询转账')]
    public function getByPrizeId(Request $request, int $prizeId): Response
    {
        $transfers = $this->service->getByPrizeId($prizeId);
        return $this->success(data: $transfers);
    }

    /**
     * 根据节点ID查询转账
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/by_node/{nodeId}')]
    #[Permission(code: 'tg_game:prize_transfer:byNode')]
    #[OperationLog('根据节点查询转账')]
    public function getByNodeId(Request $request, int $nodeId): Response
    {
        $transfer = $this->service->getByNodeId($nodeId);
        if (!$transfer) {
            return $this->error(ResultCode::NOT_FOUND, '转账记录不存在');
        }
        return $this->success(data: $transfer);
    }

    /**
     * 根据钱包地址查询转账记录
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/by_address/{address}')]
    #[Permission(code: 'tg_game:prize_transfer:byAddress')]
    #[OperationLog('根据钱包地址查询转账')]
    public function getByAddress(Request $request, string $address): Response
    {
        $limit = (int)$request->input('limit', 50);
        $transfers = $this->service->getByAddress($address, $limit);
        return $this->success(data: $transfers);
    }

    /**
     * 根据交易哈希查询转账
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/by_tx_hash/{txHash}')]
    #[Permission(code: 'tg_game:prize_transfer:byTxHash')]
    #[OperationLog('根据交易哈希查询转账')]
    public function getByTxHash(Request $request, string $txHash): Response
    {
        $transfer = $this->service->getByTxHash($txHash);
        if (!$transfer) {
            return $this->error(ResultCode::NOT_FOUND, '转账记录不存在');
        }
        return $this->success(data: $transfer);
    }

    /**
     * 获取转账统计
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/statistics')]
    #[Permission(code: 'tg_game:prize_transfer:statistics')]
    #[OperationLog('查看转账统计')]
    public function getStatistics(Request $request): Response
    {
        $groupId = $request->input('group_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $stats = $this->service->getTransferStatistics($groupId, $dateStart, $dateEnd);
        return $this->success(data: $stats);
    }

    /**
     * 导出转账记录
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/export')]
    #[Permission(code: 'tg_game:prize_transfer:export')]
    #[OperationLog('导出转账记录')]
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
            '转账ID',
            '中奖记录ID',
            '节点ID',
            '收款地址',
            '转账金额',
            '交易哈希',
            '状态',
            '重试次数',
            '错误信息',
            '创建时间',
            '更新时间',
        ];

        foreach ($records as $record) {
            $statusText = match($record->status) {
                1 => '待处理',
                2 => '处理中',
                3 => '成功',
                4 => '失败',
                default => '未知'
            };

            $exportData[] = [
                $record->id,
                $record->prize_record_id,
                $record->node_id,
                $record->to_address,
                $record->amount,
                $record->tx_hash ?? '',
                $statusText,
                $record->retry_count,
                $record->error_message ?? '',
                (string)$record->created_at,
                (string)$record->updated_at,
            ];
        }

        // 使用PhpSpreadsheet导出Excel
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($exportData, null, 'A1');

            // 设置列宽自动调整
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 设置标题行样式
            $sheet->getStyle('A1:K1')->getFont()->setBold(true);
            $sheet->getStyle('A1:K1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $fileName = 'prize_transfers_' . date('YmdHis') . '.xlsx';
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
     * 必须在 /prize_transfer/{id} 之前
     */
    #[GetMapping('/prize_transfer/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'prize_record_id',
            'node_id',
            'to_address',
            'amount',
            'status',
            'created_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 获取转账详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/prize_transfer/{id}')]
    #[Permission(code: 'tg_game:prize_transfer:detail')]
    #[OperationLog('查看转账详情')]
    public function detail(Request $request, int $id): Response
    {
        $transfer = $this->service->findById($id);
        if (!$transfer) {
            return $this->error(ResultCode::NOT_FOUND, '转账记录不存在');
        }
        return $this->success(data: $transfer);
    }

    /**
     * 手动重试转账
     */
    #[PostMapping('/prize_transfer/{id}/retry')]
    #[Permission(code: 'tg_game:prize_transfer:retry')]
    #[OperationLog('手动重试转账')]
    public function retryTransfer(Request $request, int $id): Response
    {
        $result = $this->service->retryTransfer($id);
        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 批量重试失败转账
     */
    #[PostMapping('/prize_transfer/batch_retry')]
    #[Permission(code: 'tg_game:prize_transfer:batchRetry')]
    #[OperationLog('批量重试失败转账')]
    public function batchRetry(Request $request): Response
    {
        $validator = validate($request->all(), [
            'transfer_ids' => 'required|array',
            'transfer_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->batchRetryTransfers($validatedData['transfer_ids']);

        return $this->success(data: $result);
    }

    /**
     * 手动标记转账为成功（管理员操作）
     */
    #[PostMapping('/prize_transfer/{id}/mark_success')]
    #[Permission(code: 'tg_game:prize_transfer:markSuccess')]
    #[OperationLog('标记转账为成功')]
    public function markAsSuccess(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'tx_hash' => 'sometimes|string|max:64',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->markAsSuccess($id, $validatedData['tx_hash'] ?? null);

        return $result['success']
            ? $this->success()
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 手动标记转账为失败（管理员操作）
     */
    #[PostMapping('/prize_transfer/{id}/mark_failed')]
    #[Permission(code: 'tg_game:prize_transfer:markFailed')]
    #[OperationLog('标记转账为失败')]
    public function markAsFailed(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'error_message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->markAsFailed($id, $validatedData['error_message']);

        return $result['success']
            ? $this->success()
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 更新转账状态
     */
    #[PutMapping('/prize_transfer/{id}/status')]
    #[Permission(code: 'tg_game:prize_transfer:updateStatus')]
    #[OperationLog('更新转账状态')]
    public function updateStatus(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'status' => 'required|integer|between:1,4',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->updateStatus($id, $validatedData['status']);

        return $result ? $this->success() : $this->error(ResultCode::BAD_REQUEST, '状态更新失败');
    }

    /**
     * 删除转账记录（软删除）
     */
    #[DeleteMapping('/prize_transfer')]
    #[Permission(code: 'tg_game:prize_transfer:delete')]
    #[OperationLog('删除转账记录')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    /**
     * 真实删除转账记录
     */
    #[DeleteMapping('/prize_transfer/real_delete')]
    #[Permission(code: 'tg_game:prize_transfer:realDelete')]
    #[OperationLog('真实删除转账记录')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    /**
     * 恢复回收站中的转账记录
     */
    #[PutMapping('/prize_transfer/recovery')]
    #[Permission(code: 'tg_game:prize_transfer:recovery')]
    #[OperationLog('恢复转账记录')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }
}
