<?php

namespace app\scope;

use app\model\enums\PolicyType;
use app\model\ModelDataPermissionPolicy;
use app\model\ModelUserDept;
use app\model\ModelUserPosition;
use app\model\ModelDepartment;
use app\model\ModelPosition;
use app\model\ModelTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use support\Container;
use support\Context;
use support\Db;
use support\Redis;

class TenantDataPermissionScope implements Scope
{
    // 缓存过期时间（秒）
    private const CACHE_TTL = 600;

    public function apply(Builder $builder, Model $model): Builder
    {
        $user = Context::get('user');
        if (!$user) {
            return $builder;
        }
        // 检查是否为超级管理员，如果是则不应用任何过滤
        if (!$user->isSuperAdmin()) {
            $userId = $user->id;
            $tenantIds = $this->getTenantIds($userId);

            // 如果没有获取到租户ID，则不返回任何数据
            if (empty($tenantIds)) {
                $builder->where('id', '=', 0); // 添加一个永远不成立的条件
            } else {
                $builder->whereIn('tenant_id', $tenantIds);
            }
        }
        return $builder;
    }

    /**
     * 获取用户有权限访问的租户ID列表
     *
     * @param int $userId 用户ID
     * @return array 租户ID数组
     */
    public function getTenantIds($userId): array
    {
        // 生成缓存键
        $cacheKey = "tenant_ids:user:$userId";

        // 尝试从缓存中获取
        $cachedTenantIds = $this->getFromCache($cacheKey);
        if ($cachedTenantIds !== null) {
            return $cachedTenantIds;
        }

        $modelUserDept = Container::make(ModelUserDept::class);
        $modelDataPermissionPolicy = Container::make(ModelDataPermissionPolicy::class);

        // 获取用户的数据权限策略
        $policies = $modelDataPermissionPolicy->where('user_id', $userId)->get();

        $tenantIds = [];

        // 如果没有策略，默认获取用户所在部门的租户
        if ($policies->isEmpty()) {
            $deptIds = $modelUserDept->where('user_id', $userId)->pluck('dept_id')->toArray();
            $tenantIds = array_merge($tenantIds, $this->getTenantIdsByDeptIds($deptIds, $userId, false));

            // 如果通过部门获取的租户ID为空，则通过用户岗位获取策略
            if (empty($tenantIds)) {
                $tenantIds = array_merge($tenantIds, $this->getTenantIdsByUserPositions($userId));
            }
        } else {
            foreach ($policies as $policy) {
                $policyType = $policy->policy_type;

                switch ($policyType) {
                    case PolicyType::All:
                        // ALL策略：获取所有租户ID
                        $allTenantIds = Db::table('tenant')->pluck('tenant_id')->toArray();
                        $tenantIds = array_merge($tenantIds, $allTenantIds);
                        break;

                    case PolicyType::DeptSelf:
                        // DEPT_SELF策略：获取用户所在部门的租户ID
                        $deptIds = $modelUserDept->where('user_id', $userId)->pluck('dept_id')->toArray();
                        $tenantIds = array_merge($tenantIds, $this->getTenantIdsByDeptIds($deptIds, $userId, false));
                        break;

                    case PolicyType::DeptTree:
                        // DEPT_TREE策略：获取用户所在部门及其子部门的租户ID
                        $deptIds = $modelUserDept->where('user_id', $userId)->pluck('dept_id')->toArray();
                        $tenantIds = array_merge($tenantIds, $this->getTenantIdsByDeptIds($deptIds, $userId, true));
                        break;

                    case PolicyType::CustomDept:
                        // CUSTOM_DEPT策略：获取自定义部门的租户ID
                        $customDeptIds = $policy->value ?? [];
                        $tenantIds = array_merge($tenantIds, $this->getTenantIdsByDeptIds($customDeptIds, $userId, false));
                        break;

                    case PolicyType::Self:
                        // SELF策略：只获取用户自己的数据（用户创建的租户）
                        $userTenantIds = Db::table('tenant')->where('created_by', $userId)->pluck('tenant_id')->toArray();
                        $tenantIds = array_merge($tenantIds, $userTenantIds);
                        break;

                    case PolicyType::CustomFunc:
                        // CUSTOM_FUNC策略：根据自定义逻辑获取租户ID
                        $customTenantIds = $policy->value ?? [];
                        $tenantIds = array_merge($tenantIds, $customTenantIds);
                        break;
                }
            }
        }

        // 去重并返回
        $tenantIds = array_unique($tenantIds);

        // 将结果存入缓存
        $this->saveToCache($cacheKey, $tenantIds);

        return $tenantIds;
    }

    /**
     * 通过用户岗位获取租户ID
     *
     * @param int $userId 用户ID
     * @return array 租户ID数组
     */
    private function getTenantIdsByUserPositions(int $userId): array
    {
        // 获取用户的所有岗位ID
        $positionIds = Db::table('user_position')
            ->where('user_id', $userId)
            ->pluck('position_id')
            ->toArray();

        if (empty($positionIds)) {
            return [];
        }

        // 通过岗位ID获取相关的数据权限策略
        $policies = Db::table('data_permission_policy')
            ->whereIn('position_id', $positionIds)
            ->get();

        $tenantIds = [];

        foreach ($policies as $policy) {
            $policyType = $policy->policy_type;

            switch ($policyType) {
                case PolicyType::All->value:
                    // ALL策略：获取所有租户ID
                    $allTenantIds = Db::table('tenant')->pluck('tenant_id')->toArray();
                    $tenantIds = array_merge($tenantIds, $allTenantIds);
                    break;

                case PolicyType::DeptSelf->value:
                    // DEPT_SELF策略：获取岗位所在部门的租户ID
                    $deptIds = Db::table('position')
                        ->where('id', $policy->position_id)
                        ->pluck('dept_id')
                        ->toArray();
                    $tenantIds = array_merge($tenantIds, $this->getTenantIdsByDeptIds($deptIds, $userId, false));
                    break;

                case PolicyType::DeptTree->value:
                    // DEPT_TREE策略：获取岗位所在部门及其子部门的租户ID
                    $deptIds = Db::table('position')
                        ->where('id', $policy->position_id)
                        ->pluck('dept_id')
                        ->toArray();
                    $tenantIds = array_merge($tenantIds, $this->getTenantIdsByDeptIds($deptIds, $userId, true));
                    break;

                case PolicyType::CustomDept->value:
                    // CUSTOM_DEPT策略：获取自定义部门的租户ID
                    $customDeptIds = json_decode($policy->value, true) ?? [];
                    $tenantIds = array_merge($tenantIds, $this->getTenantIdsByDeptIds($customDeptIds, $userId, false));
                    break;

                case PolicyType::Self->value:
                    // SELF策略：只获取用户自己的数据（用户创建的租户）
                    $userTenantIds = Db::table('tenant')->where('created_by', $userId)->pluck('tenant_id')->toArray();
                    $tenantIds = array_merge($tenantIds, $userTenantIds);
                    break;

                case PolicyType::CustomFunc->value:
                    // CUSTOM_FUNC策略：根据自定义逻辑获取租户ID
                    $customTenantIds = json_decode($policy->value, true) ?? [];
                    $tenantIds = array_merge($tenantIds, $customTenantIds);
                    break;
            }
        }

        return $tenantIds;
    }

    /**
     * 根据部门ID获取租户ID
     *
     * @param array $deptIds 部门ID数组
     * @param int $userId 当前用户ID
     * @param bool $includeSubDepts 是否包含子部门
     * @return array 租户ID数组
     */
    private function getTenantIdsByDeptIds(array $deptIds, int $userId, bool $includeSubDepts): array
    {
        if (empty($deptIds)) {
            // 如果没有部门，则只返回当前用户创建的租户
            return Db::table('tenant')->where('created_by', $userId)->pluck('tenant_id')->toArray();
        }

        // 根据参数决定是否递归获取所有子部门ID
        $finalDeptIds = $deptIds;
        if ($includeSubDepts) {
            $finalDeptIds = $this->getDeptTreeIds($deptIds);
        }

        // 2. 获取部门直接关联的用户IDs
        $directUserIds = Db::table('user_dept')
            ->whereIn('dept_id', $finalDeptIds)
            ->pluck('user_id')
            ->toArray();

        // 3. 获取部门下的所有岗位IDs
        $positionIds = Db::table('position')
            ->whereIn('dept_id', $finalDeptIds)
            ->pluck('id')
            ->toArray();

        // 4. 通过岗位获取关联的用户IDs
        $positionUserIds = [];
        if (!empty($positionIds)) {
            $positionUserIds = Db::table('user_position')
                ->whereIn('position_id', $positionIds)
                ->pluck('user_id')
                ->toArray();
        }

        // 5. 合并所有用户IDs并去重
        $allUserIds = array_unique(array_merge($directUserIds, $positionUserIds, [$userId]));

        // 6. 获取这些用户创建的租户IDs
        $tenantIds = Db::table('tenant')
            ->whereIn('created_by', $allUserIds)
            ->pluck('tenant_id')
            ->toArray();

        return $tenantIds;
    }

    /**
     * 获取部门树（包括子部门）
     *
     * @param array $deptIds 部门ID数组
     * @return array 包含所有子部门的部门ID数组
     */
    private function getDeptTreeIds(array $deptIds): array
    {
        if (empty($deptIds)) {
            return [];
        }

        $allDeptIds = $deptIds;
        // 递归获取子部门
        $childDeptIds = Db::table('department')
            ->whereIn('parent_id', $deptIds)
            ->pluck('id')
            ->toArray();

        if (!empty($childDeptIds)) {
            $allDeptIds = array_merge($allDeptIds, $this->getDeptTreeIds($childDeptIds));
        }

        return $allDeptIds;
    }

    /**
     * 从缓存中获取数据
     *
     * @param string $key 缓存键
     * @return array|null 缓存的数据，如果不存在则返回null
     */
    private function getFromCache(string $key): ?array
    {
        try {
            $cachedData = Redis::get($key);
            if ($cachedData !== false && $cachedData !== null) {
                return json_decode($cachedData, true);
            }
        } catch (\Exception $e) {
            // 如果Redis不可用，直接返回null，让程序继续执行
            return null;
        }
        return null;
    }

    /**
     * 将数据保存到缓存
     *
     * @param string $key 缓存键
     * @param array $data 要缓存的数据
     * @return bool 是否保存成功
     */
    private function saveToCache(string $key, array $data): bool
    {
        try {
            return Redis::setex($key, self::CACHE_TTL, json_encode($data, JSON_THROW_ON_ERROR));
        } catch (\Exception $e) {
            // 如果Redis不可用，直接返回false
            return false;
        }
    }
}