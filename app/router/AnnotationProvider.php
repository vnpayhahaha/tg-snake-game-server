<?php

declare (strict_types=1);

namespace app\router;

use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\Middleware;
use app\router\Annotations\MiddlewareIgnore;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RequestMapping;
use app\router\Annotations\ResourceMapping;
use app\router\Annotations\RestController;
use ReflectionClass;
use ReflectionMethod;
use Webman\Route;

class AnnotationProvider
{
    public static function start(): void
    {
//        dump('AnnotationProvider::start()==');

        $annotationClasses = self::scanFile();

        $tempClassAnnotations = [];
        foreach ($annotationClasses as $annotationClass) {
//            dump($annotationClass);
            $tempClassAnnotations[] = self::formatData($annotationClass);
        }
        $formatData = array_merge(...$tempClassAnnotations);
        $configMiddleware = config('middleware');
//        var_dump('$configMiddleware==',count($formatData));
//        dump($formatData);
//        dump($tempClassAnnotations);
        foreach ($formatData as $item) {
            $method = $item['method'];
            $pathPrefix = explode('/', $item['path']);
            if (isset($configMiddleware[$pathPrefix[1]]) && is_array($item['middleware'])) {
                $item['middleware'] = array_merge($item['middleware'], $configMiddleware[$pathPrefix[1]]);
            }
            // 如果 $item['method']的字符串包含逗号，则进行拆分
            if (str_contains($method, ',')) {
                $method = explode(',', $method);
            }

            if (is_array($method)) {
                Route::add($method, $item['path'], [$item['className'], $item['action']])->middleware($item['middleware']);
            } else if ($method === 'resource') {
                Route::group('', function () use ($item) {
                    Route::resource($item['path'], $item['className'], $item['allowMethods']);
                })->middleware($item['middleware']);
            } else {
                Route::$method($item['path'], [$item['className'], $item['action']])->middleware($item['middleware']);
            }
        }
//        var_dump('Route::getRoutes()==', Route::getRoutes());
    }

    private static function scanFile()
    {
        $suffix = config('app.controller_suffix', '');
        $suffixLength = strlen($suffix);
        $scanFolders = config("annotation.include_paths");

        // 递归扫描目录的函数
        $scanDirectory = function ($dir, $depth = 0) use (&$scanDirectory, $suffix, $suffixLength) {
            $files = [];
            $indent = str_repeat('  ', $depth);

            // error_log("{$indent}扫描目录: $dir");

            // 检查目录是否存在和可读
            if (!is_dir($dir)) {
                // error_log("{$indent}目录不存在: $dir");
                return $files;
            }

            if (!is_readable($dir)) {
                // error_log("{$indent}目录不可读: $dir");
                return $files;
            }

            // 获取目录内容
            $items = scandir($dir);
            if ($items === false) {
                // error_log("{$indent}扫描目录失败: $dir");
                return $files;
            }

            // error_log("{$indent}找到 " . count($items) . " 个项目");

            foreach ($items as $item) {
                // 跳过 . 和 ..
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $path = $dir . DIRECTORY_SEPARATOR . $item;

                // 如果是目录，递归扫描
                if (is_dir($path)) {
                    // error_log("{$indent}进入子目录: $item");
                    $files = array_merge($files, $scanDirectory($path, $depth + 1));
                    continue;
                }

                // 检查是否为 PHP 文件
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if (strtolower($extension) !== 'php') {
                    // error_log("{$indent}跳过非 PHP 文件: $item");
                    continue;
                }

                // 获取不带扩展名的文件名
                $fileName = pathinfo($path, PATHINFO_FILENAME);

                // 后缀检查
                if ($suffixLength > 0 && substr($fileName, -$suffixLength) !== $suffix) {
                    // error_log("{$indent}跳过文件（后缀不匹配）: $item");
                    continue;
                }

                // error_log("{$indent}找到符合条件的文件: $item");
                $files[] = $path;
            }

            // error_log("{$indent}目录扫描完成，找到 " . count($files) . " 个符合条件的文件");

            return $files;
        };

        foreach ($scanFolders as $scanFolder) {
            $controllerPath = base_path("$scanFolder/controller");

            // error_log("开始扫描控制器目录: $controllerPath");

            // 扫描目录获取所有 PHP 文件
            $files = $scanDirectory($controllerPath);

            // error_log("总共找到 " . count($files) . " 个符合条件的文件");

            foreach ($files as $filePath) {
                // 构建类名
                $basePath = base_path();
                $relativePath = substr($filePath, strlen($basePath));
                $className = str_replace(['/', '.php'], ['\\', ''], $relativePath);
                $className = ltrim($className, '\\'); // 移除开头的反斜杠

                // error_log("尝试加载类: $className from: $filePath");

                if (!class_exists($className)) {
                    // error_log("类不存在: $className");
                    continue;
                }

                // error_log("成功加载类: $className");
                yield $className;
            }
        }
    }

    private static function formatData($annotationClass)
    {
        $class = new ReflectionClass($annotationClass);
        $resourceMatch = false;
        $classAllowMethods = [];
        $className = $class->name;
        $tempClassAnnotations = [];
        $classPrefix = '';

        /** @var \ReflectionAttribute $classControllerAnnotation */
        $classControllerAnnotations = $class->getAttributes(RestController::class);
        if ($classControllerAnnotations) {
            foreach ($classControllerAnnotations as $classControllerAnnotation) {
                $classControllerAnnotationArgs = $classControllerAnnotation->getArguments();
                $classPrefix = $classControllerAnnotationArgs['path'] ?? current($classControllerAnnotationArgs) ?: '';
            }
        }


        $classMiddlewares = [];
        /** @var \ReflectionAttribute $classMiddlewareAnnotation */
        $classMiddlewareAnnotations = $class->getAttributes(Middleware::class);
        if ($classMiddlewareAnnotations) {
            foreach ($classMiddlewareAnnotations as $classMiddlewareAnnotation) {
                $args = $classMiddlewareAnnotation->getArguments();
                if (is_string($args[0])) {
                    $classMiddlewares[] = [$args[0]];
                } elseif (is_array($args[0])) {
                    $classMiddlewares[] = $args[0];
                }
            }
            $classMiddlewares = array_merge(...$classMiddlewares);
        }

        /** @var \ReflectionAttribute $classResourceAnnotation */
        $classResourceAnnotations = $class->getAttributes(ResourceMapping::class);
        if ($classResourceAnnotations) {
            foreach ($classResourceAnnotations as $classResourceAnnotation) {
                $classResourceAnnotationArgs = $classResourceAnnotation->getArguments();
                $classPath = $classPrefix . ($classResourceAnnotationArgs['path'] ?? $classResourceAnnotationArgs[0] ?? '');
                $classAllowMethods = $classResourceAnnotationArgs['allow_methods'] ?? [];
                $tempClassAnnotations[] = [
                    'method'       => 'resource',
                    'className'    => $className,
                    'path'         => $classPath,
                    'allowMethods' => $classAllowMethods,
                    'middleware'   => $classMiddlewares,
                ];
            }
            $resourceMatch = true;
        }

        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $item) {
            $action = $item->name;
            if ($resourceMatch && self::checkResourceAction($action, $classAllowMethods)) {
                continue;
            }

            $methodIgnoreMiddlewareAllSign = false;
            $methodIgnoreMiddlewares = [];
            $methodIgnoreMiddlewareAnnotations = $item->getAttributes(MiddlewareIgnore::class);
            if ($methodIgnoreMiddlewareAnnotations) {
                /** @var \ReflectionAttribute $methodMiddlewareAnnotation */
                foreach ($methodIgnoreMiddlewareAnnotations as $methodIgnoreMiddlewareAnnotation) {
                    $args = $methodIgnoreMiddlewareAnnotation->getArguments();
                    if (!empty($args)) {
                        if (is_string($args[0])) {
                            $methodIgnoreMiddlewares[] = [$args[0]];
                        } elseif (is_array($args[0])) {
                            $methodIgnoreMiddlewares[] = $args[0];
                        }
                    }
                }
                $methodIgnoreMiddlewares = array_merge(...$methodIgnoreMiddlewares);
                !$methodIgnoreMiddlewares && $methodIgnoreMiddlewareAllSign = true;
            }

            $methodMiddlewares = [];
            $methodMiddlewareAnnotations = $item->getAttributes(Middleware::class);
            if ($methodMiddlewareAnnotations) {
                /** @var \ReflectionAttribute $methodMiddlewareAnnotation */
                foreach ($methodMiddlewareAnnotations as $methodMiddlewareAnnotation) {
                    $args = $methodMiddlewareAnnotation->getArguments();
                    if (is_string($args[0])) {
                        $methodMiddlewares[] = [$args[0]];
                    } elseif (is_array($args[0])) {
                        $methodMiddlewares[] = $args[0];
                    }
                }
                $methodMiddlewares = array_merge(...$methodMiddlewares);
            }

            $methodMappingAnnotations = [
                $item->getAttributes(RequestMapping::class),
                $item->getAttributes(GetMapping::class),
                $item->getAttributes(PostMapping::class),
                $item->getAttributes(PutMapping::class),
                $item->getAttributes(DeleteMapping::class),
            ];

            foreach ($methodMappingAnnotations as $mappingAnnotation) {
                if ($mappingAnnotation) {
                    /** @var \ReflectionAttribute $item */
                    foreach ($mappingAnnotation as $item) {
                        $itemArgs = $item->getArguments();
                        $mappingPaths = $itemArgs['path'] ?? $itemArgs[0] ?? '';
                        if (is_string($mappingPaths)) {
                            $mappingPaths = [$mappingPaths];
                        }
                        if ($item->getName() === RequestMapping::class) {
                            $method = $itemArgs['methods'];
                            if (is_array($method)) {
                                array_walk($method, function (&$m) {
                                    $m = strtoupper($m);
                                });
                            }
                        } else {
                            $method = $item->newInstance()->getMethods();
                        }
                        foreach ($mappingPaths as $mappingPath) {
                            $allMiddlewares = array_merge($classMiddlewares, $methodMiddlewares);

                            if (!empty($methodIgnoreMiddlewares)) {
                                $allMiddlewares = array_diff($allMiddlewares, $methodIgnoreMiddlewares);
                            }

                            if ($methodIgnoreMiddlewareAllSign) {
                                $allMiddlewares = [];
                            }

                            $tempClassAnnotations[] = [
                                'method'     => $method,
                                'path'       => $classPrefix . $mappingPath,
                                'className'  => $className,
                                'action'     => $action,
                                'middleware' => $allMiddlewares,
                            ];
                        }
                    }
                }
            }
        }

        return $tempClassAnnotations;
    }

    private static function checkResourceAction(string $action, array $allowActions = []): bool
    {
        $actions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'recovery'];
        if ($allowActions) {
            $actions = array_intersect($actions, $allowActions);
        }
        return in_array($action, $actions, true);
    }
}
