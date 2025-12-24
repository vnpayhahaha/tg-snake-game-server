<?php

namespace app\repository;

use app\model\ModelBankDisbursementUpload;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BankDisbursementDownloadRepository.
 * @extends IRepository<ModelBankDisbursementUpload>
 */
class BankDisbursementUploadRepository  extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementUpload $model;

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

    /**
     * 通过hash获取上传文件的信息.
     */
    public function getFileInfoByHash(string $hash, array $columns = ['*']): ?ModelBankDisbursementUpload
    {
        $model = $this->model::query()->where('hash', $hash)->first($columns);
        if (!$model) {
            return null;
        }
        return $model;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('channel:id,channel_name,channel_code,channel_icon')
            ->paginate(
            perPage: $pageSize,
            pageName: static::PER_PAGE_PARAM_NAME,
            page: $page,
        );
        return $this->handlePage($result);
    }
}
