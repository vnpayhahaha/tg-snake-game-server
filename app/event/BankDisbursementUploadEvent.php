<?php

namespace app\event;

use app\constants\BankDisbursementUpload;
use app\model\ModelBankDisbursementUpload;
use support\Container;

class BankDisbursementUploadEvent
{
    public function Created(ModelBankDisbursementUpload $model): void
    {
        var_dump('ModelBankDisbursementUpload sleep 10s start', microtime(true));
        $upload_bill_template_id = $model->upload_bill_template_id;
        $bill_config = config('bankbill.' . $upload_bill_template_id->value);
        if (!filled($bill_config)) {
            throw new \RuntimeException('bank bill config is empty');
        }
        $upload_service_class = Container::get($bill_config['upload_service_class']);
        try {
            $upload_service_class->importBill($model);
        } catch (\Throwable $e) {
            var_dump('ModelBankDisbursementUpload error for upload_bill_template_id:' . $upload_bill_template_id->value, $e->getMessage());
        }
        $updateModel = ModelBankDisbursementUpload::query()->where('id', $model->id)->first();
        if ($updateModel->record_count === $updateModel->success_count) {
            $updateModel->parsing_status = BankDisbursementUpload::PARSING_STATUS_SUCCESS;
            $updateModel->save();
        }
        var_dump('ModelBankDisbursementUpload sleep 10s end', microtime(true));
    }
}