<?php

namespace app\service;

use app\exception\ResourceNotFoundException;
use app\lib\enum\ResultCode;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementUploadRepository;
use DI\Attribute\Inject;
use support\Response;

class BankDisbursementUploadService extends IService
{
    #[Inject]
    public BankDisbursementUploadRepository $repository;
    #[Inject]
    public AttachmentService $attachmentService;

    public function upload($file, array $params): array
    {
        return $this->attachmentService->chunkUpload($file, $params, 'bill');
    }

    public function download(int $id): Response
    {
        /** @var ModelBankDisbursementUpload $filesInfo */
        $filesInfo = $this->repository->getModel()->where(['id' => $id])->first();
        if ($filesInfo) {
            $filename = $filesInfo->file_name;
            $result = (new Response(200, [
                'Server'                        => env('APP_NAME', 'LangDaLang'),
                'access-control-expose-headers' => 'content-disposition',
            ]))->download(public_path() . $filesInfo->path, $filename)
                ->header('Content-Disposition', "attachment; filename={$filename}; filename*=UTF-8''" . rawurlencode($filename));
            return $result;
        }
        throw new ResourceNotFoundException(ResultCode::NOT_FOUND);
    }
}
