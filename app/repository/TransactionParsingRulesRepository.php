<?php

namespace app\repository;

use app\constants\TransactionParsingLog;
use app\constants\TransactionParsingRules;
use app\model\ModelTransactionParsingLog;
use app\model\ModelTransactionParsingRules;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;


class TransactionParsingRulesRepository extends IRepository
{
    #[Inject]
    protected ModelTransactionParsingRules $model;
    #[Inject]
    protected ModelTransactionParsingLog $modelTransactionParsingLog;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['channel_id']) && filled($params['channel_id'])) {
            $query->where('channel_id', $params['channel_id']);
        }

        if (isset($params['variable_name']) && filled($params['variable_name'])) {
            $query->where('variable_name', $params['variable_name']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)->with('channel:id,channel_name,channel_code,channel_icon')->paginate(
            perPage: $pageSize,
            pageName: static::PER_PAGE_PARAM_NAME,
            page: $page,
        );
        return $this->handlePage($result);
    }

    public function regularParsing(int $raw_data_id, int $raw_data_channel_id, string $raw_data_content): array
    {
        $ruleList = $this->getModel()->query()
            ->where('channel_id', $raw_data_channel_id)
            ->where('status', TransactionParsingRules::STATUS_ENABLE)
            ->orderBy('id', 'asc')
            ->get()->toArray();
        // 记录信息
        $result = [];
        foreach ($ruleList as $rule) {
            $fail_msg = [];
            $fail_msg_en = [];
            if (preg_match_all("/{$rule['regex']}/", $raw_data_content, $matches, PREG_SET_ORDER)) {
                //var_dump($matches);
                // 过滤空项并赋值
                $non_empty_matches = array_filter($matches[0], static function ($value) {
                    return !empty($value) || $value === '0';
                });
                // 存储第一个完整匹配数据
                $original_match = $matches[0][0];
                // 移除第一个元素，因为$match[0]是完整匹配
                array_shift($non_empty_matches);
                $countVar = count($rule['variable_name']);
                $countMatches = count($non_empty_matches);
                if ($countMatches !== $countVar) {
                    $fail_msg = [
                        "[未匹配规则({$rule['id']})：变量数量[{$countVar}]与规则变量数量[$countMatches]不一致(`{$rule['regex']}`)]",
                        $original_match
                    ];
                    $fail_msg_en = [
                        "[Unmatched rule({$rule['id']})：The number of variables[{$countVar}] does not match the number of rule variables[$countMatches] (`{$rule['regex']}`)]",
                        $original_match
                    ];
                } else {
                    $fail_msg = [
                        "[已匹配规则({$rule['id']})：规则匹配成功(`{$rule['regex']}`)]",
                        $original_match
                    ];
                    $fail_msg_en = [
                        "[Rule matched ({$rule['id']}): Rule matching successful (`{$rule['regex']}`)]",
                        $original_match
                    ];
                    $result = array_combine($rule['variable_name'], $non_empty_matches);
                    // 记录解析日志
                    $this->modelTransactionParsingLog->newQuery()->create([
                        'raw_data_id'   => $raw_data_id,
                        'rule_id'       => $rule['id'],
                        'rule_text'     => $rule['regex'],
                        'variable_name' => $rule['variable_name'],
                        'status'        => TransactionParsingLog::STATUS_SUCCESS,
                        'fail_msg'      => json_encode($fail_msg, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                        'fail_msg_en'   => json_encode($fail_msg_en, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    ]);
                    break;
                }
            } else {
                $fail_msg = [
                    "[未匹配规则({$rule['id']})：规则匹配失败(`{$rule['regex']}`)]",
                    $raw_data_content
                ];
                $fail_msg_en = [
                    "[Unmatched rule({$rule['id']})：Rule matching failed (`{$rule['regex']}`)]",
                    $raw_data_content
                ];
            }

            // 记录解析日志
            $this->modelTransactionParsingLog->newQuery()->create([
                'raw_data_id'   => $raw_data_id,
                'rule_id'       => $rule['id'],
                'rule_text'     => $rule['regex'],
                'variable_name' => $rule['variable_name'],
                'status'        => TransactionParsingLog::STATUS_FAIL,
                'fail_msg'      => json_encode($fail_msg, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'fail_msg_en'   => json_encode($fail_msg_en, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
        }

        return $result;
    }
}
