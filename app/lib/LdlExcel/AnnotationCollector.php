<?php

namespace app\lib\LdlExcel;

use app\lib\annotation\ExcelProperty;
use ReflectionClass;
use ReflectionProperty;

class AnnotationCollector
{
    private static array $cache = [];

    public static function get(string $className): ?array
    {
        if (isset(self::$cache[$className])) {
            return self::$cache[$className];
        }

        try {
            $reflectionClass = new ReflectionClass($className);
            $result = [
                '_c' => $className,
                '_p' => []
            ];

            $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
            
            foreach ($properties as $property) {
                $attributes = $property->getAttributes(ExcelProperty::class);
                if (!empty($attributes)) {
                    $propertyName = $property->getName();
                    $result['_p'][$propertyName] = [];
                    
                    foreach ($attributes as $attribute) {
                        $instance = $attribute->newInstance();
                        $result['_p'][$propertyName][ExcelProperty::class] = $instance;
                    }
                }
            }

            self::$cache[$className] = $result;
            return $result;
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    public static function clearCache(string $className = null): void
    {
        if ($className === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$className]);
        }
    }
}