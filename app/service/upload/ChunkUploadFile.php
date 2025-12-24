<?php

namespace app\service\upload;

use Webman\Http\UploadFile;
use support\Db;

class ChunkUploadFile
{
// 分片存储路径
    protected string $chunkPath;

    // 文件存储路径
    protected string $filePath;
    protected string $pathName;

    public function __construct()
    {
        $this->chunkPath = runtime_path() . '/chunks/';
        $this->pathName = '/upload/' . date('Ymd') . '/';
        $this->filePath = public_path() . $this->pathName;

        // 确保目录存在
        if (!is_dir($this->chunkPath) && !mkdir($concurrentDirectory = $this->chunkPath, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        if (!is_dir($this->filePath) && !mkdir($concurrentDirectory = $this->filePath, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }

    // 获取分片存储路径
    public function getChunkPath($fileId, $index): string
    {
        return $this->chunkPath . $fileId . '_' . $index . '.chunk';
    }

    // 获取文件存储路径
    public function getFilePath($fileName, $fileId): string
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return $this->filePath . $fileId . '.' . $ext;
    }

    public function setPathName(string $pathName): ChunkUploadFile
    {
        if (filled($pathName)) {
            $this->pathName = $pathName;
            $filePath = public_path() . '/upload/' . $this->pathName . '/';
            if (!is_dir($filePath) && !mkdir($concurrentDirectory = $filePath, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $this->filePath = $filePath;
        }
        return $this;
    }

    // 设置文件存储路径
    public function setFilePath(string $filePath): ChunkUploadFile
    {
        if (!is_dir($filePath) && !mkdir($concurrentDirectory = $filePath, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $this->filePath = $filePath;
        return $this;
    }

    // 保存分片
    public function saveChunk(UploadFile $file, array $data): bool
    {
        $chunkPath = $this->getChunkPath($data['fileId'], $data['index']);

        // 移动分片到临时目录
        if ($file->move($chunkPath)) {
            // 记录分片上传信息到数据库（可选）
            $this->recordChunk($data);
            return true;
        }

        return false;
    }

    // 合并分片
    public function mergeChunks(string $fileId, string $fileName, int $totalChunks, string $fileHash, int $fileSize, string $fileType): array
    {
        $filePath = $this->getFilePath($fileName, $fileId);

        // 检查所有分片是否存在
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkPath = $this->getChunkPath($fileId, $i);
            if (!file_exists($chunkPath)) {
                throw new \RuntimeException("Shard {$i} does not exist");
            }
        }

        // 打开目标文件
        $dest = fopen($filePath, 'wb');
        if (!$dest) {
            throw new \RuntimeException("Unable to create a file {$filePath}");
        }

        // 按顺序合并分片
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkPath = $this->getChunkPath($fileId, $i);
            $source = fopen($chunkPath, 'rb');
            if (!$source) {
                fclose($dest);
                throw new \RuntimeException("Unable to open shards {$i}");
            }

            stream_copy_to_stream($source, $dest);
            fclose($source);

            // 删除分片
            unlink($chunkPath);
        }

        fclose($dest);

        // 验证文件大小
        if (filesize($filePath) !== $fileSize) {
            unlink($filePath);
            throw new \RuntimeException("File size mismatch");
        }

        // 验证文件哈希（可选）
        // if (md5_file($filePath) !== $fileHash) {
        //     unlink($filePath);
        //     throw new \Exception("文件哈希不匹配");
        // }

        // base_path等于 $filePath 去掉前面 public_path()
        $base_path = str_replace(public_path(), '', $filePath);

        // 生成文件信息
        $fileInfo = [
            'name'      => $fileName,
            'type'      => $fileType,
            'size'      => $fileSize,
            'path'      => $filePath,
            'base_path' => $base_path,
            'hash'      => $fileHash,
            'extension' => strtolower(pathinfo($fileName, PATHINFO_EXTENSION)),
        ];

        // 记录文件信息到数据库（可选）
        $this->recordFile($fileInfo);

        return $fileInfo;
    }

    // 记录分片信息到数据库（可选）
    protected function recordChunk(array $data): void
    {
        // 这里可以添加数据库操作，记录分片上传信息
        // 用于断点续传和上传状态跟踪
        // Db::table('upload_chunks')->insert([
        //     'file_id' => $data['fileId'],
        //     'chunk_index' => $data['index'],
        //     'total_chunks' => $data['total'],
        //     'file_name' => $data['name'],
        //     'file_size' => $data['size'],
        //     'file_hash' => $data['hash'],
        //     'created_at' => date('Y-m-d H:i:s')
        // ]);
    }

    // 记录文件信息到数据库（可选）
    protected function recordFile(array $fileInfo): void
    {
        // 这里可以添加数据库操作，记录已上传的文件信息
        // Db::table('upload_files')->insert([
        //     'name' => $fileInfo['name'],
        //     'type' => $fileInfo['type'],
        //     'size' => $fileInfo['size'],
        //     'path' => $fileInfo['path'],
        //     'url' => $fileInfo['url'],
        //     'hash' => $fileInfo['hash'],
        //     'created_at' => date('Y-m-d H:i:s')
        // ]);
    }
}