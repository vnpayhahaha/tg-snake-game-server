<?php

namespace app\repository;

use app\model\ModelBankDisbursementDownload;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BankDisbursementDownloadRepository.
 * @extends IRepository<ModelBankDisbursementDownload>
 */
class BankDisbursementDownloadRepository  extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementDownload $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['file_name']) && filled($params['file_name'])) {
            $query->where('file_name', $params['file_name']);
        }

        if (isset($params['hash']) && filled($params['hash'])) {
            $query->where('hash', $params['hash']);
        }

        return $query;
    }
}
