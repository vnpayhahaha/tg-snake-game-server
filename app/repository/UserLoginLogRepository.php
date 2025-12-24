<?php

namespace app\repository;

use app\model\ModelUserLoginLog;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * Class UserRepository.
 * @extends IRepository<ModelUserLoginLog>
 */
final class UserLoginLogRepository extends IRepository
{
    #[Inject]
    protected  ModelUserLoginLog $model;


    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(Arr::get($params, 'username'), static function (Builder $query, $username) {
                $query->where('username', $username);
            })
            ->when(Arr::get($params, 'ip'), static function (Builder $query, $ip) {
                $query->where('ip', $ip);
            })
            ->when(Arr::get($params, 'os'), static function (Builder $query, $os) {
                $query->where('os', $os);
            })
            ->when(Arr::get($params, 'browser'), static function (Builder $query, $browser) {
                $query->where('browser', $browser);
            })
            ->when(Arr::get($params, 'status'), static function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when(Arr::get($params, 'message'), static function (Builder $query, $message) {
                $query->where('message', $message);
            })
            ->when(Arr::get($params, 'login_time'), static function (Builder $query, $login_time) {
                $query->whereBetween('login_time', $login_time);
            })
            ->when(Arr::get($params, 'remark'), static function (Builder $query, $remark) {
                $query->where('remark', $remark);
            });
    }
}
