<?php

namespace app\model\casts;


use app\model\fieldExpansion\ModelMenuMeta;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use support\Model;

class MetaCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ModelMenuMeta
    {
        return new ModelMenuMeta(empty($value) ? [] : Json::decode($value));
    }

    /**
     * @param MetaCast $value
     * @param Model $model
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return Json::encode($value);
    }
}
