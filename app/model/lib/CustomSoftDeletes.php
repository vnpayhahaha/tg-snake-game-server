<?php

namespace app\model\lib;

use Illuminate\Database\Eloquent\SoftDeletes;

trait CustomSoftDeletes
{
    use SoftDeletes;

    /**
     * 自定义软删除逻辑
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());
        $time = $this->freshTimestamp();

        // 基础要更新的字段
        $columns = [
            $this->getDeletedAtColumn() => $this->fromDateTime($time),
        ];

        // 检查是否存在 deleted_by 属性
        if ($this->hasDeletedByColumn() && isset($this->deleted_by)) {
            $columns['deleted_by'] = $this->deleted_by;
        }

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->usesTimestamps() && !is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
        $this->fireModelEvent('trashed', false);
    }

    /**
     * 检查模型是否有 deleted_by 属性
     *
     * @return bool
     */
    protected function hasDeletedByColumn(): bool
    {
        // 检查模型是否定义了该属性
        return property_exists($this, 'deleted_by') ||
            in_array('deleted_by', $this->getFillable()) ||
            in_array('deleted_by', $this->getAttributes());
    }
}
