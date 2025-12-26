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
use app\service\TgPrizeDispatchQueueService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * Telegram游戏中奖派发队列控制器
 */
#[RestController("/admin/tg_game")]
class TgPrizeDispatchQueueController extends BasicController
{
    #[Inject]
    protected TgPrizeDispatchQueueService $service;

    /**
     * 中奖派发队列列表
     */
    #[GetMapping('/dispatch_queue/list')]
    #[Permission(code: 'tg_game:dispatch_queue:list')]
    #[OperationLog('中奖派发队列列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: $this->service->getDispatchQueuePage(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }

    /**
     * 获取待处理的派发队列
     * 必须在 /dispatch_queue/{id} 之前
     */
    #[GetMapping('/dispatch_queue/pending')]
    #[Permission(code: 'tg_game:dispatch_queue:pending')]
    #[OperationLog('查看待处理派发队列')]
    public function getPending(Request $request): Response
    {
        $queues = $this->service->getPendingDispatchQueues();
        return $this->success(data: $queues);
    }

    /**
     * 获取处理中的派发队列
     * 必须在 /dispatch_queue/{id} 之前
     */
    #[GetMapping('/dispatch_queue/processing')]
    #[Permission(code: 'tg_game:dispatch_queue:processing')]
    #[OperationLog('查看处理中派发队列')]
    public function getProcessing(Request $request): Response
    {
        $queues = $this->service->getProcessingDispatchQueues();
        return $this->success(data: $queues);
    }

    /**
     * 获取失败的派发队列
     * 必须在 /dispatch_queue/{id} 之前
     */
    #[GetMapping('/dispatch_queue/failed')]
    #[Permission(code: 'tg_game:dispatch_queue:failed')]
    #[OperationLog('查看失败派发队列')]
    public function getFailed(Request $request): Response
    {
        $queues = $this->service->getFailedDispatchQueues();
        return $this->success(data: $queues);
    }

    /**
     * 根据中奖记录ID查询派发队列
     * 必须在 /dispatch_queue/{id} 之前
     */
    #[GetMapping('/dispatch_queue/by_prize/{prizeId}')]
    #[Permission(code: 'tg_game:dispatch_queue:byPrize')]
    #[OperationLog('根据中奖记录查询派发队列')]
    public function getByPrizeId(Request $request, int $prizeId): Response
    {
        $queue = $this->service->getDispatchQueueByPrizeId($prizeId);
        if (!$queue) {
            return $this->error(ResultCode::NOT_FOUND, '派发队列不存在');
        }
        return $this->success(data: $queue);
    }

    /**
     * 根据群组ID查询派发队列
     * 必须在 /dispatch_queue/{id} 之前
     */
    #[GetMapping('/dispatch_queue/by_group/{groupId}')]
    #[Permission(code: 'tg_game:dispatch_queue:byGroup')]
    #[OperationLog('根据群组查询派发队列')]
    public function getByGroupId(Request $request, int $groupId): Response
    {
        $limit = (int)$request->input('limit', 50);
        $queues = $this->service->getDispatchQueuesByGroup($groupId, $limit);
        return $this->success(data: $queues);
    }

    /**
     * 获取派发队列统计
     * 必须在 /dispatch_queue/{id} 之前
     */
    #[GetMapping('/dispatch_queue/statistics')]
    #[Permission(code: 'tg_game:dispatch_queue:statistics')]
    #[OperationLog('查看派发队列统计')]
    public function getStatistics(Request $request): Response
    {
        $groupId = $request->input('group_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $stats = $this->service->getDispatchQueueStatistics($groupId, $dateStart, $dateEnd);
        return $this->success(data: $stats);
    }

    /**
     * 获取远程搜索数据
     * 必须在 /dispatch_queue/{id} 之前
     */
    #[GetMapping('/dispatch_queue/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'prize_record_id',
            'group_id',
            'status',
            'retry_count',
            'created_at',
        ];
        return $this->success(
            $this->service->getDispatchQueueList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    /**
     * 获取派发队列详情
     * 必须在所有具体路径之后
     */
    #[GetMapping('/dispatch_queue/{id}')]
    #[Permission(code: 'tg_game:dispatch_queue:detail')]
    #[OperationLog('查看派发队列详情')]
    public function detail(Request $request, int $id): Response
    {
        $queue = $this->service->getDispatchQueueById($id);
        if (!$queue) {
            return $this->error(ResultCode::NOT_FOUND, '派发队列不存在');
        }
        return $this->success(data: $queue);
    }

    /**
     * 手动重试派发
     */
    #[PostMapping('/dispatch_queue/{id}/retry')]
    #[Permission(code: 'tg_game:dispatch_queue:retry')]
    #[OperationLog('手动重试派发')]
    public function retryDispatch(Request $request, int $id): Response
    {
        $result = $this->service->retryDispatchQueue($id);
        return $result['success']
            ? $this->success(data: $result)
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 批量重试失败派发
     */
    #[PostMapping('/dispatch_queue/batch_retry')]
    #[Permission(code: 'tg_game:dispatch_queue:batchRetry')]
    #[OperationLog('批量重试失败派发')]
    public function batchRetry(Request $request): Response
    {
        $validator = validate($request->all(), [
            'queue_ids' => 'required|array',
            'queue_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->batchRetryDispatchQueues($validatedData['queue_ids']);

        return $this->success(data: $result);
    }

    /**
     * 手动标记为成功（管理员操作）
     */
    #[PostMapping('/dispatch_queue/{id}/mark_success')]
    #[Permission(code: 'tg_game:dispatch_queue:markSuccess')]
    #[OperationLog('标记派发为成功')]
    public function markAsSuccess(Request $request, int $id): Response
    {
        $result = $this->service->markDispatchQueueSuccess($id);
        return $result['success']
            ? $this->success()
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 手动标记为失败（管理员操作）
     */
    #[PostMapping('/dispatch_queue/{id}/mark_failed')]
    #[Permission(code: 'tg_game:dispatch_queue:markFailed')]
    #[OperationLog('标记派发为失败')]
    public function markAsFailed(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'error_message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->markDispatchQueueFailed($id, $validatedData['error_message']);

        return $result['success']
            ? $this->success()
            : $this->error(ResultCode::BAD_REQUEST, $result['message']);
    }

    /**
     * 更新派发队列状态
     */
    #[PutMapping('/dispatch_queue/{id}/status')]
    #[Permission(code: 'tg_game:dispatch_queue:updateStatus')]
    #[OperationLog('更新派发队列状态')]
    public function updateStatus(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'status' => 'required|integer|between:1,4',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validatedData = $validator->validate();
        $result = $this->service->updateDispatchQueueStatus($id, $validatedData['status']);

        return $result ? $this->success() : $this->error(ResultCode::BAD_REQUEST, '状态更新失败');
    }

    /**
     * 删除派发队列（软删除）
     */
    #[DeleteMapping('/dispatch_queue')]
    #[Permission(code: 'tg_game:dispatch_queue:delete')]
    #[OperationLog('删除派发队列')]
    public function delete(Request $request): Response
    {
        $this->service->deleteDispatchQueue($request->all());
        return $this->success();
    }

    /**
     * 真实删除派发队列
     */
    #[DeleteMapping('/dispatch_queue/real_delete')]
    #[Permission(code: 'tg_game:dispatch_queue:realDelete')]
    #[OperationLog('真实删除派发队列')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDeleteDispatchQueue((array)$request->all()) ? $this->success() : $this->error();
    }
}
