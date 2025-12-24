<?php

namespace app\service\upload;


use app\exception\UploadException;
use app\service\SystemConfigService;
use support\Container;

/**
 * 文件上传
 *
 * @author Mr.April
 * @since  1.0
 * @method static uploadFile()
 */
class UploadFile
{

    static array $allowStorage = [];

    protected static function init(): void
    {
        $configAllowStorage = config('upload.adapter_classes');
        self::$allowStorage = array_unique(array_merge([
            'local',
            'oss',
            'cos',
            'qiniu',
            's3',
        ], array_keys($configAllowStorage)));
    }

    /**
     * 获取配置信息
     *
     * @param string $name
     *
     * @return array|null
     */
    public static function getConfig(string $name = ''): array
    {
        $config = config('upload.config', []);
        if (empty($name)) {
            return [];
        }
        return $config[$name] ?? [];
    }

    /**
     * 获取默认配置
     *
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        $settingConfigService = Container::make(SystemConfigService::class);
        $basicConfig = $settingConfigService->getDetails([
            'group_id' => 2,
        ])->toArray();

        if (empty($basicConfig)) {
            return [
                'mode'         => 'local',
                'single_limit' => 1024,
                'total_limit'  => 1024,
                'nums'         => 1,
                'include'      => ['png'],
                'exclude'      => ['mp4'],
            ];
        }
        $basicConfigKeyValue = array_column($basicConfig, 'value', 'key');

        $upload_include = $basicConfigKeyValue['upload_include'] ?? '';
        $upload_exclude = $basicConfigKeyValue['upload_exclude'] ?? '';
        return [
            'mode'         => $basicConfigKeyValue['upload_mode'] ?? 'local',
            'single_limit' => $basicConfigKeyValue['upload_single_limit'] ?? 1024,
            'total_limit'  => $basicConfigKeyValue['upload_total_limit'] ?? 1024,
            'nums'         => $basicConfigKeyValue['upload_nums'] ?? 1,
            'include'      => $basicConfigKeyValue['upload_include'] ? explode(',', $upload_include) : ['png'],
            'exclude'      => $basicConfigKeyValue['upload_exclude'] ? explode(',', $upload_exclude) : ['mp4'],
        ];
    }

    public static function disk(string|null $storage = null, bool $is_file_upload = true): UploadFileInterface
    {
        self::init();
        $defaultConfig = self::getDefaultConfig();
        if (empty($storage)) {
            $adapter = $defaultConfig['mode'];
            $adapterConfig = self::getConfig($adapter);
        } else {
            $adapter = $storage;
            $adapterConfig = self::getConfig($storage);
        }
        if (!in_array($adapter, self::$allowStorage)) {
            throw new UploadException("不支持的存储类型:" . $adapter);
        }
        $config = array_merge($defaultConfig, $adapterConfig, ['_is_file_upload' => $is_file_upload]);
        $handle = config('upload.adapter_classes.' . $adapter);
        if (!$handle) {
            throw new UploadException("未找到适配器处理器:" . $handle);
        }
        return new $handle($config);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return static::disk()->{$name}(...$arguments);
    }

}
