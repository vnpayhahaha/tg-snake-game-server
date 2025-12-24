<?php

namespace app\service;

use app\exception\BusinessException;
use app\exception\UnprocessableEntityException;
use app\exception\UploadException;
use app\lib\annotation\DataScope;
use app\lib\enum\ResultCode;
use app\model\enums\ScopeType;
use app\repository\AttachmentRepository;
use app\service\upload\ChunkUploadFile;
use app\service\upload\UploadFile;
use DI\Attribute\Inject;
use support\Context;
use support\Db;
use Webman\Http\Request;


final class AttachmentService extends IService
{
    #[Inject]
    protected AttachmentRepository $repository;

    #[Inject]
    public ChunkUploadFile $chunkUploadFile;


    public function getRepository(): AttachmentRepository
    {
        return $this->repository;
    }

    #[DataScope(
        scopeType: ScopeType::SELF,
    )]
    public function page(array $params, int $page = 1, int $pageSize = 10): array
    {
        return parent::page($params, $page, $pageSize);
    }

    public function upload(string $upload = '', bool $isLocal = false): ?array
    {
        try {
            $resource = Db::transaction(function () use ($upload, $isLocal) {
                $baseConfig = UploadFile::getDefaultConfig();//获取上次配置
                if (empty($baseConfig)) {
                    throw new UploadException('缺少上传配置信息');
                }

                $type = $baseConfig['mode'] ?? 'local';//上次模式默认本地
                if ($isLocal) {
                    $type = 'local';
                }

                $result = UploadFile::uploadFile();
                $data = $result[0];
                $hash = $data['unique_id'];

                $url = str_replace('\\', '/', $data['url']);
                $path = str_replace('\\', '/', $data['save_path']);

                // 检查文件是否已存在
                if ($filesInfo = $this->repository->getModel()->where(['hash' => $hash])->first()) {
                    return $filesInfo->toArray();
                }
                $inData = [
                    'storage_mode' => $type,
                    'origin_name'  => $data['origin_name'] ?? '',
                    'object_name'  => $data['save_name'],
                    'hash'         => $hash,
                    'mime_type'    => $data['mime_type'],
                    'base_path'    => $data['base_path'],
                    'suffix'       => strtolower($data['extension']),
                    'size_byte'    => $data['size'],
                    'size_info'    => formatBytes($data['size']),
                    'url'          => $url,
                    'storage_path' => $path,
                ];
                $result = $this->repository->getModel()->create($inData);
                if (!$result) {
                    return [];
                }
                return $result->toArray();
            });
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
        return $resource;
    }

    public function chunkUpload($file, array $params, string $directory = ''): array
    {
        // 验证参数
        if (!$params['fileId'] ||
            !$params['index'] ||
            !$params['total'] ||
            !$params['fileName'] ||
            !$params['fileSize'] ||
            !$params['fileType'] ||
            !$params['fileHash']) {
            throw new UploadException(ResultCode::UNPROCESSABLE_ENTITY);
        }

        if (!($file instanceof \Webman\Http\UploadFile)) {
            throw new UploadException(ResultCode::UNPROCESSABLE_ENTITY);
        }
        // 查询 hash 是否存在
        if ($filesInfo = $this->repository->getModel()->where(['hash' => $params['fileHash']])->first()) {
            return $filesInfo->toArray();
        }

        try {
            // 保存分片
            $saveChunkOK = $this->chunkUploadFile->saveChunk($file, [
                'fileId' => $params['fileId'],
                'index'  => $params['index'],
                'total'  => $params['total'],
                'name'   => $params['fileName'],
                'size'   => $params['fileSize'],
                'hash'   => $params['fileHash'],
                'type'   => $params['fileType'],
            ]);
        } catch (\RuntimeException $e) {
            throw new UploadException(ResultCode::UPLOAD_CHUNK_FAILED, $e->getMessage());
        }

        if (!$saveChunkOK) {
            throw new UploadException(ResultCode::UPLOAD_CHUNK_FAILED);
        }
        $result['chunk'] = $params['index'];
        // 如果是最后一个分片，则合并
        if ($params['index'] === $params['total']) {
            try {
                $result = $this->chunkUploadFile->setPathName($directory)->mergeChunks($params['fileId'], $params['fileName'], (int)$params['total'], $params['fileHash'], (int)$params['fileSize'], $params['fileType']);
                if ($result && filled($result)) {
                    // 记录文件信息到数据库
                    $data = [
                        'storage_mode' => 'local',
                        'origin_name'  => $params['fileName'],
                        'object_name'  => $params['fileHash'] . '.' . $result['extension'],
                        'hash'         => $params['fileHash'],
                        'mime_type'    => $params['fileType'],
                        'storage_path' => $result['path'],
                        'base_path'    => $result['base_path'],
                        'suffix'       => $result['extension'],
                        'url'          => env('APP_DOMAIN', '') . $result['base_path'],
                        'size_byte'    => $params['fileSize'],
                        'size_info'    => formatBytes($params['fileSize']),
                    ];
                    $request = Context::get(Request::class);
                    $user = $request->user ?? null;
                    if (filled($user)) {
                        $data['created_by'] = $user->id;
                    }
                    $filesInfo = $this->repository->create($data);
                    if ($filesInfo) {
                        return $filesInfo->toArray();
                    }
                }
            } catch (\RuntimeException $e) {
                throw new UploadException(ResultCode::UPLOAD_FAILED, $e->getMessage());
            }
        }
        return $result;
    }
}
