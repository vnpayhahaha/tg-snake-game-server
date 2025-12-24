<?php

namespace app\lib\traits;

use app\lib\annotation\Message;
use ReflectionClass;


trait ConstantsTrait
{
    /**
     * 根据错误码获取对应的消息
     *
     * @param int|string $code 错误码
     * @return string 对应的消息
     */
    public static function getMessage(int|string $code): string
    {
        $reflection = new ReflectionClass(static::class);

        // 检查类是否是枚举
        if ($reflection->isEnum()) {

            foreach ($reflection->getReflectionConstants() as $constant) {
                $constantValue = $constant->getValue();
                $constantName = $constant->getName();
                // 确保我们处理的是枚举案例
                if ($constantValue instanceof \UnitEnum) {
                    $constantValue = $constantValue->value;
                }
                if ($constantValue === $code) {
                    $attributes = $constant->getAttributes(Message::class);
                    //var_dump('==$attributes==', $attributes);
                    if (!empty($attributes)) {
                        /** @var Message $messageAnnotation */
                        $messageAnnotation = $attributes[0]->newInstance();
                        $messageKey = $messageAnnotation->getMessage();

                        //var_dump('==message==', $messageKey);

                        // trans($messageTranKey,  [],$domain); 如果有$messageKey已.分割，第一部分为domain,剩余部分为$messageTranKey，尝试进行翻译
                        if (str_contains($messageKey, '.')) {
                            $messageTranKey = substr($messageKey, strpos($messageKey, '.') + 1);
                            $domain = substr($messageKey, 0, strpos($messageKey, '.'));
                            //var_dump('==domain==', $messageTranKey, $domain);
                            $messageKey = trans($messageTranKey, [], $domain);
                        }


                        return $messageKey;
                    }
                    break;
                }
            }
        }

        // 如果没有找到消息注解，返回默认值或枚举名称
        return (string)$code;
    }

    /**
     * 获取枚举值
     */
    public static function getValue(): int
    {
        return static::class::cases()[0]->value;
    }

}
