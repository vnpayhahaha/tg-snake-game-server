<?php

namespace app\event;

use app\repository\DisbursementOrderStatusRecordsRepository;
use DI\Attribute\Inject;

class DisbursementOrderStatusRecordsEvent
{
    #[Inject]
    protected DisbursementOrderStatusRecordsRepository $repository;

    /**
     * // int $order_id, int $status, string $desc_cn, string $desc_en, string $remark
     * @param array $data
     * @return void
     */
    public function process(array $data): void
    {
        if (is_array($data['order_id'])) {
            foreach ($data['order_id'] as $order_id) {
                $this->runCreate([
                    'order_id' => $order_id,
                    'status'   => $data['status'],
                    'desc_cn'  => $data['desc_cn'],
                    'desc_en'  => $data['desc_en'],
                    'remark'   => $data['remark'] ?? '',
                ]);
            }
        } else {
            $this->runCreate([
                'order_id' => $data['order_id'],
                'status'   => $data['status'],
                'desc_cn'  => $data['desc_cn'],
                'desc_en'  => $data['desc_en'],
                'remark'   => $data['remark'] ?? '',
            ]);
        }
    }

    protected function runCreate(array $data)
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