<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
return [
    'success'                                            => '成功',
    'unknown'                                            => '服务器异常',
    'fail'                                               => '失败',
    'bad_request'                                        => '请求失败',
    'unauthorized'                                       => '未登录',
    'token_refresh_expired'                              => 'token已过期',
    'forbidden'                                          => '无权限',
    'not_found'                                          => '数据不存在',
    'method_not_allowed'                                 => '方法不允许',
    'not_acceptable'                                     => '不可接受',
    'request_timeout'                                    => '请求超时',
    'conflict'                                           => '请求冲突',
    'payload_too_large'                                  => '请求体过大',
    'unprocessable_entity'                               => '请求参数错误',
    'disabled'                                           => '账号已禁用',
    'enum_not_found'                                     => '枚举不存在',
    'upload_failed'                                      => '上传失败',
    'upload_chunk_failed'                                => '分片上传失败',
    'excel_parse_failed'                                 => 'Excel解析失败',
    'invalid_channel'                                    => '无效的渠道',

    // 用户模块
    'user_login_failed'                                  => '用户登录失败',
    'user_not_exist'                                     => '用户不存在',
    'role_not_exist'                                     => '角色不存在',
    'user_num_limit_exceeded'                            => '用户数量超出限制',
    'tenant_account_not_exist'                           => '租户账号不存在',
    'user_google_2fa_verify_failed'                      => '双因素验证码验证失败',

    // openapi
    'openapi.system_error'                               => '系统错误',
    'openapi.sign_is_required'                           => '需要签名',
    'openapi.app_key_is_required'                        => '需要App key',
    'openapi.app_key_is_invalid'                         => '应用程序密钥无效',
    'openapi.sign_is_invalid'                            => '签名无效',
    'openapi.timestamp_is_required'                      => '需要时间戳',
    'openapi.timestamp_is_expired'                       => '时间戳已过期',
    'openapi.app_is_disabled'                            => '应用程序已禁用',

    // 订单模块
    'order.no_available_collection_method'               => '没有可用的收款方式',
    'order.no_matching_bank_card'                        => '没有匹配可用的银行卡',
    'order.collection_float_amount_error'                => '收款浮动金额配置错误',
    'order.collection_amount_less_than_min_float_amount' => '收款金额不能小于配置的浮动金额最小值',
    'order.not_found'                                    => '订单不存在',
    'order.status_error'                                 => '订单状态错误',
    'order.verify_failed'                                => '订单核销失败',
    'order.bank_bill_template_not_exist'                 => '银行帐单模板不存在',
    'order.bank_bill_template_runtime_error'             => '银行帐单模板运行时错误',
    'order.tenant_not_open_receipt'                      => '当前租户没有开启收款功能',
    'order.tenant_not_open_payment'                      => '当前租户没有开启付款功能',

    // 交易凭证
    'transaction.trade_voucher_not_exist'                => '交易凭证不存在',
];
