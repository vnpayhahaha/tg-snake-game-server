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
use app\service\TgSnakeNodeService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏蛇身节点管理控制器
 */
#[RestController("/admin/tg_game")]
class TgSnakeNodeController extends BasicController
{
    #[Inject]
    protected TgSnakeNodeService $service;

    /**
     * 蛇身节点列表
     */
    #[GetMapping('/snake_node/list')]
    #[Permission(code: 'tg_game:snake_node:list')]
    #[OperationLog('蛇身节点列表')]
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
     * 获取群组活跃蛇身节点
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/active/{groupId}')]
    #[Permission(code: 'tg_game:snake_node:active')]
    #[OperationLog('查看活跃蛇身节点')]
    public function getActiveNodes(Request $request, int $groupId): Response
    {
        $nodes = $this->service->getActiveNodesByGroup($groupId);
        return $this->success(data: $nodes);
    }

    /**
     * 获取钱包周期蛇身节点
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/by_wallet_cycle/{groupId}/{walletCycle}')]
    #[Permission(code: 'tg_game:snake_node:byWalletCycle')]
    #[OperationLog('查看钱包周期蛇身节点')]
    public function getNodesByWalletCycle(Request $request, int $groupId, int $walletCycle): Response
    {
        $nodes = $this->service->getNodesByWalletCycle($groupId, $walletCycle);
        return $this->success(data: $nodes);
    }

    /**
     * 获取已归档的蛇身节点
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/archived/{groupId}')]
    #[Permission(code: 'tg_game:snake_node:archived')]
    #[OperationLog('查看已归档蛇身节点')]
    public function getArchivedNodes(Request $request, int $groupId): Response
    {
        $limit = (int)$request->input('limit', 100);
        $nodes = $this->service->getArchivedNodesByGroup($groupId, $limit);
        return $this->success(data: $nodes);
    }

    /**
     * 获取玩家的购彩记录
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/by_player/{groupId}/{walletAddress}')]
    #[Permission(code: 'tg_game:snake_node:byPlayer')]
    #[OperationLog('查看玩家购彩记录')]
    public function getNodesByPlayer(Request $request, int $groupId, string $walletAddress): Response
    {
        $limit = (int)$request->input('limit', 50);
        $nodes = $this->service->getNodesByPlayer($groupId, $walletAddress, $limit);
        return $this->success(data: $nodes);
    }

    /**
     * 根据流水号查询节点
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/by_serial/{serialNo}')]
    #[Permission(code: 'tg_game:snake_node:bySerial')]
    #[OperationLog('根据流水号查询节点')]
    public function getBySerialNo(Request $request, string $serialNo): Response
    {
        $node = $this->service->getBySerialNo($serialNo);
        if (!$node) {
            return $this->error(ResultCode::NOT_FOUND, '节点不存在');
        }
        return $this->success(data: $node);
    }

    /**
     * 根据交易哈希查询节点
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/by_tx_hash/{txHash}')]
    #[Permission(code: 'tg_game:snake_node:byTxHash')]
    #[OperationLog('根据交易哈希查询节点')]
    public function getByTxHash(Request $request, string $txHash): Response
    {
        $node = $this->service->getByTxHash($txHash);
        if (!$node) {
            return $this->error(ResultCode::NOT_FOUND, '节点不存在');
        }
        return $this->success(data: $node);
    }

    /**
     * 获取当日节点统计
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/daily_statistics/{groupId}')]
    #[Permission(code: 'tg_game:snake_node:dailyStatistics')]
    #[OperationLog('查看当日节点统计')]
    public function getDailyStatistics(Request $request, int $groupId): Response
    {
        $date = $request->input('date', date('Y-m-d'));
        $stats = $this->service->getDailyStatistics($groupId, $date);
        return $this->success(data: $stats);
    }

    /**
     * 获取群组节点统计
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/group_statistics/{groupId}')]
    #[Permission(code: 'tg_game:snake_node:groupStatistics')]
    #[OperationLog('查看群组节点统计')]
    public function getGroupStatistics(Request $request, int $groupId): Response
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $stats = $this->service->getGroupStatistics($groupId, $dateStart, $dateEnd);
        return $this->success(data: $stats);
    }

    /**
     * 导出蛇身节点记录
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/export')]
    #[Permission(code: 'tg_game:snake_node:export')]
    #[OperationLog('导出蛇身节点记录')]
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
            '节点ID',
            '群组ID',
            '群组名称',
            '凭证流水号',
            '购彩凭证',
            '钱包地址',
            'Telegram用户名',
            'Telegram用户ID',
            '投注金额',
            '交易哈希',
            '区块高度',
            '节点序号',
            '每日序号',
            '钱包周期',
            '状态',
            '中奖记录ID',
            '创建时间',
        ];

        foreach ($records as $record) {
            $statusText = match($record->status) {
                1 => '活跃',
                2 => '已中奖',
                3 => '已取消',
                4 => '已归档',
                default => '未知'
            };

            $exportData[] = [
                $record->id,
                $record->group_id,
                $record->group_name ?? '',
                $record->ticket_serial_no,
                $record->ticket_number,
                $record->player_address,
                $record->player_tg_username ?? '',
                $record->player_tg_user_id ?? '',
                $record->amount,
                $record->tx_hash,
                $record->block_height,
                $record->node_index,
                $record->daily_sequence,
                $record->wallet_cycle,
                $statusText,
                $record->matched_prize_id ?? '',
                (string)$record->created_at,
            ];
        }

        // 使用PhpSpreadsheet导出Excel
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($exportData, null, 'A1');

            // 设置列宽自动调整
            foreach (range('A', 'Q') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 设置标题行样式
            $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
            $sheet->getStyle('A1:Q1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $fileName = 'snake_nodes_' . date('YmdHis') . '.xlsx';
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
     * 必须在 /snake_node/{id} 之前
     */
    #[GetMapping('/snake_node/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'group_id',
            'ticket_serial_no',
            'ticket_number',
            'player_address',
            'amount',
            'status',
            'created_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 获取蛇身节点详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/snake_node/{id}')]
    #[Permission(code: 'tg_game:snake_node:detail')]
    #[OperationLog('查看蛇身节点详情')]
    public function detail(Request $request, int $id): Response
    {
        $node = $this->service->findById($id);
        if (!$node) {
            return $this->error(ResultCode::NOT_FOUND, '节点不存在');
        }
        return $this->success(data: $node);
    }

    /**
     * 手动归档节点（管理员操作）
     */
    #[PostMapping('/snake_node/{id}/archive')]
    #[Permission(code: 'tg_game:snake_node:archive')]
    #[OperationLog('手动归档节点')]
    public function archiveNode(Request $request, int $id): Response
    {
        $result = $this->service->archiveNode($id);
        return $result['success']
            ? $this->success()
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 批量归档节点
     */
    #[PostMapping('/snake_node/batch_archive')]
    #[Permission(code: 'tg_game:snake_node:batchArchive')]
    #[OperationLog('批量归档节点')]
    public function batchArchive(Request $request): Response
    {
        $validator = validate($request->all(), [
            'node_ids' => 'required|array',
            'node_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->batchArchiveNodes($validatedData['node_ids']);

        return $this->success(data: $result);
    }

    /**
     * 更新节点状态
     */
    #[PutMapping('/snake_node/{id}/status')]
    #[Permission(code: 'tg_game:snake_node:updateStatus')]
    #[OperationLog('更新节点状态')]
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
     * 删除蛇身节点（软删除）
     */
    #[DeleteMapping('/snake_node')]
    #[Permission(code: 'tg_game:snake_node:delete')]
    #[OperationLog('删除蛇身节点')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    /**
     * 真实删除节点
     */
    #[DeleteMapping('/snake_node/real_delete')]
    #[Permission(code: 'tg_game:snake_node:realDelete')]
    #[OperationLog('真实删除节点')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    /**
     * 恢复回收站中的节点
     */
    #[PutMapping('/snake_node/recovery')]
    #[Permission(code: 'tg_game:snake_node:recovery')]
    #[OperationLog('恢复节点')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }
}
