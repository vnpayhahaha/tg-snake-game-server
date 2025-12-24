<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\TgPrizeService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏中奖记录管理控制器
 */
#[RestController("/admin/tg_game")]
class TgPrizeRecordController extends BasicController
{
    #[Inject]
    protected TgPrizeService $service;

    /**
     * 中奖记录列表
     */
    #[GetMapping('/prize/list')]
    #[Permission(code: 'tg_game:prize:list')]
    #[OperationLog('中奖记录列表')]
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
     * 获取待处理的中奖记录
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/pending')]
    #[Permission(code: 'tg_game:prize:pending')]
    #[OperationLog('查看待处理中奖记录')]
    public function getPendingRecords(Request $request): Response
    {
        $records = $this->service->getPendingRecords();
        return $this->success(data: $records);
    }

    /**
     * 获取转账中的记录
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/transferring')]
    #[Permission(code: 'tg_game:prize:transferring')]
    #[OperationLog('查看转账中记录')]
    public function getTransferringRecords(Request $request): Response
    {
        $records = $this->service->getTransferringRecords();
        return $this->success(data: $records);
    }

    /**
     * 导出中奖记录
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/export')]
    #[Permission(code: 'tg_game:prize:export')]
    #[OperationLog('导出中奖记录')]
    public function export(Request $request): Response
    {
        // TODO: 实现导出功能
        return $this->error(ResultCode::NOT_IMPLEMENTED, '导出功能待实现');
    }

    /**
     * 获取远程搜索数据
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'group_id',
            'prize_serial_no',
            'prize_amount',
            'winner_count',
            'status',
            'created_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 根据流水号查询中奖记录
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/by_serial/{serialNo}')]
    #[Permission(code: 'tg_game:prize:bySerial')]
    #[OperationLog('根据流水号查询中奖记录')]
    public function getBySerialNo(Request $request, string $serialNo): Response
    {
        $prize = $this->service->getBySerialNo($serialNo);
        if (!$prize) {
            return $this->error(ResultCode::NOT_FOUND, '中奖记录不存在');
        }
        return $this->success(data: $prize);
    }

    /**
     * 获取群组中奖记录
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/by_group/{groupId}')]
    #[Permission(code: 'tg_game:prize:byGroup')]
    #[OperationLog('查看群组中奖记录')]
    public function getByGroupId(Request $request, int $groupId): Response
    {
        $limit = (int)$request->input('limit', 20);
        $prizes = $this->service->getByGroupId($groupId, $limit);
        return $this->success(data: $prizes);
    }

    /**
     * 获取钱包周期中奖记录
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/by_wallet_cycle/{groupId}/{walletCycle}')]
    #[Permission(code: 'tg_game:prize:byWalletCycle')]
    #[OperationLog('查看钱包周期中奖记录')]
    public function getByWalletCycle(Request $request, int $groupId, int $walletCycle): Response
    {
        $prizes = $this->service->getByWalletCycle($groupId, $walletCycle);
        return $this->success(data: $prizes);
    }

    /**
     * 获取群组中奖统计
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/statistics/{groupId}')]
    #[Permission(code: 'tg_game:prize:statistics')]
    #[OperationLog('查看群组中奖统计')]
    public function getStatistics(Request $request, int $groupId): Response
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $stats = $this->service->getGroupStatistics($groupId, $dateStart, $dateEnd);
        return $this->success(data: $stats);
    }

    /**
     * 获取当日中奖统计
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/daily_statistics/{groupId}')]
    #[Permission(code: 'tg_game:prize:dailyStatistics')]
    #[OperationLog('查看当日中奖统计')]
    public function getDailyStatistics(Request $request, int $groupId): Response
    {
        $date = $request->input('date', date('Y-m-d'));
        $stats = $this->service->getDailyStatistics($groupId, $date);
        return $this->success(data: $stats);
    }

    /**
     * 获取最近中奖记录
     * 必须在 /prize/{id} 之前
     */
    #[GetMapping('/prize/recent/{groupId}')]
    #[Permission(code: 'tg_game:prize:recent')]
    #[OperationLog('查看最近中奖记录')]
    public function getRecentPrizes(Request $request, int $groupId): Response
    {
        $limit = (int)$request->input('limit', 10);
        $prizes = $this->service->getRecentPrizes($groupId, $limit);
        return $this->success(data: $prizes);
    }

    /**
     * 获取中奖记录详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/prize/{id}')]
    #[Permission(code: 'tg_game:prize:detail')]
    #[OperationLog('查看中奖详情')]
    public function detail(Request $request, int $id): Response
    {
        $prize = $this->service->findById($id);
        if (!$prize) {
            return $this->error(ResultCode::NOT_FOUND, '中奖记录不存在');
        }
        return $this->success(data: $prize);
    }

    /**
     * 获取中奖记录的转账详情
     */
    #[GetMapping('/prize/{id}/transfers')]
    #[Permission(code: 'tg_game:prize:transfers')]
    #[OperationLog('查看中奖转账详情')]
    public function getTransfers(Request $request, int $id): Response
    {
        $transfers = $this->service->getPrizeTransfers($id);
        return $this->success(data: $transfers);
    }

    /**
     * 批量重试失败的转账
     * 必须在 /prize/{id} 之前
     */
    #[PostMapping('/prize/retry_failed_transfers')]
    #[Permission(code: 'tg_game:prize:retryFailedTransfers')]
    #[OperationLog('批量重试失败转账')]
    public function retryFailedTransfers(Request $request): Response
    {
        $validator = validate($request->all(), [
            'prize_ids' => 'required|array',
            'prize_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->batchRetryFailedTransfers($validatedData['prize_ids']);

        return $this->success(data: $result);
    }

    /**
     * 手动重新处理中奖派发
     */
    #[PostMapping('/prize/{id}/reprocess')]
    #[Permission(code: 'tg_game:prize:reprocess')]
    #[OperationLog('重新处理中奖派发')]
    public function reprocessPrize(Request $request, int $id): Response
    {
        $result = $this->service->reprocessPrize($id);
        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 更新中奖记录状态
     */
    #[PutMapping('/prize/{id}/status')]
    #[Permission(code: 'tg_game:prize:updateStatus')]
    #[OperationLog('更新中奖记录状态')]
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
}
