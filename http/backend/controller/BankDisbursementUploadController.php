<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\model\enums\DisbursementOrderBillTemplate;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\BankDisbursementUploadService;
use DI\Attribute\Inject;
use Illuminate\Validation\Rule;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class BankDisbursementUploadController extends BasicController
{
    #[Inject]
    protected BankDisbursementUploadService $service;

    #[GetMapping('/bank_disbursement_upload/list')]
    #[Permission(code: 'transaction:bank_disbursement_upload:list')]
    #[OperationLog('银行账单下载记录列表')]
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

    // 上传账单
    #[PostMapping('/bank_disbursement_upload/upload')]
    #[Permission(code: 'transaction:bank_disbursement_upload:upload')]
    #[OperationLog('上传银行账单')]
    public function upload(Request $request): Response
    {
        $file = $request->file('file');
        return $this->success(
            data: $this->service->upload(
                $file,
                $request->all(),
            )
        );
    }

    // create
    #[PostMapping('/bank_disbursement_upload')]
    #[Permission(code: 'transaction:bank_disbursement_upload:create')]
    #[OperationLog('创建银行账单')]
    public function create(Request $request): Response
    {
        $params = $request->all();
        $validator = validate($params, [
            'channel_id'              => [
                'required',
                'integer',
                'between:1,99999999999'
            ],
            'upload_bill_template_id' => [
                'required',
                'string',
                'max:50',
                Rule::enum(DisbursementOrderBillTemplate::class),
            ],
            'attachment_id'           => 'required|integer|between:0,999999999',
            'file_name'               => [
                'required',
                'string',
                'max:200'
            ],
            'path'                    => [
                'required',
                'string',
                'max:200'
            ],
            'hash'                    => [
                'required',
                'string',
                'max:64',
                function ($attribute, $value, $fail) use ($params) {
                    if ($this->service->repository->getFileInfoByHash($value)) {
                        $fail(trans('unique', [':attribute' => $params['file_name']], 'validation'));
                    }
                }
            ],
            'file_size'               => [
                'required',
                'string',
                'max:64'
            ],
            'suffix'                  => [
                'required',
                'string',
                'max:32'
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->create(array_merge(
            $validatedData,
            [
                'created_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    #[PostMapping('/bank_disbursement_upload/download/{id}')]
    public function download(Request $request, int $id): Response
    {
        return $this->service->download($id);
    }
}