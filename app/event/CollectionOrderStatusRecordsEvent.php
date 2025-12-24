<?php

namespace app\event;

use app\repository\CollectionOrderStatusRecordsRepository;
use DI\Attribute\Inject;

class CollectionOrderStatusRecordsEvent
{
    #[Inject]
    protected CollectionOrderStatusRecordsRepository $repository;

    /**
     * // int $order_id, int $status, string $desc_cn, string $desc_en, string $remark
     * @param array $data
     * @return \app\model\ModelCollectionOrderStatusRecords|\app\repository\Model|mixed
     */
    public function process(array $data): mixed
    {
        return $this->repository->create([
            'order_id'   => $data['order_id'],
            'status'     => $data['status'],
            'desc_cn'    => $data['desc_cn'],
            'desc_en'    => $data['desc_en'],
            'remark'     => $data['remark'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}