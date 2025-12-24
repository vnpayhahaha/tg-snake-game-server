<?php

namespace app\lib\annotation;


use app\model\enums\ScopeType;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class DataScope
{
    public function __construct(
        private readonly string $deptColumn = 'dept_id',
        private readonly string $createdByColumn = 'created_by',
        private readonly ScopeType $scopeType = ScopeType::SELF,
        private readonly ?array $onlyTables = null
    ) {}

    public function getOnlyTables(): ?array
    {
        return $this->onlyTables;
    }

    public function getDeptColumn(): string
    {
        return $this->deptColumn;
    }

    public function getCreatedByColumn(): string
    {
        return $this->createdByColumn;
    }

    public function getScopeType(): ScopeType
    {
        return $this->scopeType;
    }
}
