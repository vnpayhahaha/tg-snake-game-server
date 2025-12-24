<?php

namespace app\service;

use app\exception\ResourceNotFoundException;
use app\lib\enum\ResultCode;
use app\model\ModelBankDisbursementDownload;
use app\repository\BankDisbursementDownloadRepository;
use DI\Attribute\Inject;
use support\Response;

class BankDisbursementDownloadService extends IService
{
    #[Inject]
    public BankDisbursementDownloadRepository $repository;

    public function download(int $id): Response
    {
        /** @var ModelBankDisbursementDownload $filesInfo */
        $filesInfo = $this->repository->getModel()->where(['id' => $id])->first();
        if ($filesInfo) {
            $filename = $filesInfo->file_name . '.'.$filesInfo->suffix ;
            $result = (new Response(200, [
                'Server'                        => env('APP_NAME', 'LangDaLang'),
                'access-control-expose-headers' => 'content-disposition',
            ]))->download(BASE_PATH . $filesInfo->path, $filename)
                ->header('Content-Disposition', "attachment; filename={$filename}; filename*=UTF-8''" . rawurlencode($filename));
            $this->repository->getModel()->where(['id' => $id])->increment('record_count', 1);
            return $result;
        }
        throw new ResourceNotFoundException(ResultCode::NOT_FOUND);
    }
}
