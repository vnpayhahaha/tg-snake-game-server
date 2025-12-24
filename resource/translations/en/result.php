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
    'success'                                            => 'Success',
    'unknown'                                            => 'Unknown',
    'fail'                                               => 'Fail',
    'bad_request'                                        => 'Bad Request',
    'unauthorized'                                       => 'Unauthorized',
    'token_refresh_expired'                              => 'Login Token Expired',
    'forbidden'                                          => 'Forbidden',
    'not_found'                                          => 'Not Found',
    'method_not_allowed'                                 => 'Method Not Allowed',
    'not_acceptable'                                     => 'Not Acceptable',
    'request_timeout'                                    => 'Request timeout',
    'conflict'                                           => 'Request conflict',
    'payload_too_large'                                  => 'Payload too large',
    'unprocessable_entity'                               => 'Request parameter error',
    'disabled'                                           => 'Account disabled',
    'enum_not_found'                                     => 'Enum not found',
    'upload_failed'                                      => 'Upload failed',
    'upload_chunk_failed'                                => 'Failed to upload shards',
    'excel_parse_failed'                                 => 'Excel parsing failed',
    'invalid_channel'                                    => 'Invalid channel',

    // 用户模块
    'user_login_failed'                                  => 'User login failed',
    'user_not_exist'                                     => 'User not exist',
    'role_not_exist'                                     => 'Role not exist',
    'user_num_limit_exceeded'                            => 'User number limit exceeded',
    'tenant_account_not_exist'                           => 'Tenant account not exist',
    'user_google_2fa_verify_failed'                      => 'Two-factor verification code verification failed',

    // openapi
    'openapi.system_error'                               => 'System error',
    'openapi.sign_is_required'                           => 'Sign is required',
    'openapi.app_key_is_required'                        => 'App key is required',
    'openapi.app_key_is_invalid'                         => 'App key is invalid',
    'openapi.sign_is_invalid'                            => 'Sign is invalid',
    'openapi.timestamp_is_required'                      => 'Timestamp is required',
    'openapi.timestamp_is_expired'                       => 'Timestamp is expired',
    'openapi.app_is_disabled'                            => 'App is disabled',

    // 订单模块
    'order.no_available_collection_method'               => 'No available collection method',
    'order.no_matching_bank_card'                        => 'No matching bank card',
    'order.collection_float_amount_error'                => 'Collection float amount error',
    'order.collection_amount_less_than_min_float_amount' => 'Collection amount less than min float amount',
    'order.not_found'                                    => 'Order does not exist',
    'order.status_error'                                 => 'Order status error',
    'order.verify_failed'                                => 'The order verification failed',
    'order.bank_bill_template_not_exist'                 => 'The bank statement template does not exist',
    'order.bank_bill_template_runtime_error'             => 'The bank statement template runtime error',
    'order.tenant_not_open_receipt'                      => 'The current tenant does not enable the collection function',
    'order.tenant_not_open_payment'                      => 'The current tenant does not enable the payment function',

    // 交易凭证
    'transaction.trade_voucher_not_exist'                => 'The transaction voucher does not exist',
];
