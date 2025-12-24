<?php

namespace app\lib\enum;

use app\lib\annotation\Message;
use app\lib\traits\ConstantsTrait;

enum ResultCode: int
{
    use ConstantsTrait;

    // System Level Errors
    #[Message('result.success')]
    case SUCCESS = 200;

    #[Message('result.unknown')]
    case UNKNOWN = 100500;

    #[Message('result.fail')] // 503
    case FAIL = 100503;

    #[Message('result.bad_request')] // 400
    case BAD_REQUEST = 100400;

    #[Message('result.unauthorized')]
    case UNAUTHORIZED = 100401;

    #[Message('result.token_refresh_expired')]
    case TOKEN_REFRESH_EXPIRED = 100402;

    #[Message('result.forbidden')]
    case FORBIDDEN = 100403;

    #[Message('result.not_found')]
    case NOT_FOUND = 100404;

    #[Message('result.method_not_allowed')]
    case METHOD_NOT_ALLOWED = 100405;

    #[Message('result.not_acceptable')]
    case NOT_ACCEPTABLE = 100406;

    #[Message('result.request_timeout')]
    case REQUEST_TIMEOUT = 100408;

    #[Message('result.conflict')]
    case CONFLICT = 100409;

    #[Message('result.payload_too_large')]
    case PAYLOAD_TOO_LARGE = 100413;

    #[Message('result.unprocessable_entity')]
    case UNPROCESSABLE_ENTITY = 100422;

    // Business Logic Validation Errors
    #[Message('result.disabled')]
    case DISABLED = 101001;
    // Invalid channel
    #[Message('result.invalid_channel')]
    case INVALID_CHANNEL = 101002;

    // 字段枚举获取失败
    #[Message('result.enum_not_found')]
    case ENUM_NOT_FOUND = 102001;

    // UPLOAD_CHUNK_FAILED
    #[Message('result.upload_failed')]
    case UPLOAD_FAILED = 103001;
    #[Message('result.upload_chunk_failed')]
    case UPLOAD_CHUNK_FAILED = 103002;
    // EXCEL_PARSE_FAILED
    #[Message('result.excel_parse_failed')]
    case EXCEL_PARSE_FAILED = 104001;

    // backend 错误码 2xxxxx  系统标识【1】-业务模块标识【2】-错误码【3】

    // 用户模块 201xxx
    #[Message('result.user_login_failed')]
    case USER_LOGIN_FAILED = 201001;
    // USER_NOT_FOUND
    #[Message('result.user_not_exist')]
    case USER_NOT_EXIST = 201002;
    // role_not_exist
    #[Message('result.role_not_exist')]
    case ROLE_NOT_EXIST = 201003;
    // USER_NUM_LIMIT_EXCEEDED
    #[Message('result.user_num_limit_exceeded')]
    case USER_NUM_LIMIT_EXCEEDED = 201004;
    // TENANT_ACCOUNT_NOT_EXIST
    #[Message('result.tenant_account_not_exist')]
    case TENANT_ACCOUNT_NOT_EXIST = 201005;
    // USER_GOOGLE_2FA_VERIFY_FAILED
    #[Message('result.user_google_2fa_verify_failed')]
    case USER_GOOGLE_2FA_VERIFY_FAILED = 201006;

    // openApi
    // sign is required
    #[Message('result.openapi.system_error')]
    case OPENAPI_SYSTEM_ERROR = 202000;
    #[Message('result.openapi.sign_is_required')]
    case OPENAPI_SIGN_IS_REQUIRED = 202001;
    // app_key is required
    #[Message('result.openapi.app_key_is_required')]
    case OPENAPI_APP_KEY_IS_REQUIRED = 202002;
    // app_key is invalid
    #[Message('result.openapi.app_key_is_invalid')]
    case OPENAPI_APP_KEY_IS_INVALID = 202003;
    // sign is invalid
    #[Message('result.openapi.sign_is_invalid')]
    case OPENAPI_SIGN_IS_INVALID = 202004;
    // timestamp is required
    #[Message('result.openapi.timestamp_is_required')]
    case OPENAPI_TIMESTAMP_IS_REQUIRED = 202005;
    // timestamp is expired
    #[Message('result.openapi.timestamp_is_expired')]
    case OPENAPI_TIMESTAMP_IS_EXPIRED = 202006;
    // app is disabled
    #[Message('result.openapi.app_is_disabled')]
    case OPENAPI_APP_IS_DISABLED = 202007;

    // 订单模块
    // 没有可用的收款方式
    #[Message('result.order.no_available_collection_method')]
    case ORDER_NO_AVAILABLE_COLLECTION_METHOD = 203001;
    // 没有匹配可用的银行卡
    #[Message('result.order.no_matching_bank_card')]
    case ORDER_NO_MATCHING_BANK_CARD = 203002;
    // 收款浮动金额配置错误
    #[Message('result.order.collection_float_amount_error')]
    case ORDER_COLLECTION_FLOAT_AMOUNT_ERROR = 203003;
    // 收款金额不能小于配置的浮动金额最小值
    #[Message('result.order.collection_amount_less_than_min_float_amount')]
    case ORDER_COLLECTION_AMOUNT_LESS_THAN_MIN_FLOAT_AMOUNT = 203004;
    // ORDER_CREATE_FAILED
    #[Message('result.order.create_failed')]
    case ORDER_CREATE_FAILED = 203005;
    // ORDER_NOT_FOUND
    #[Message('result.order.not_found')]
    case ORDER_NOT_FOUND = 203006;
    // ORDER_STATUS_ERROR
    #[Message('result.order.status_error')]
    case ORDER_STATUS_ERROR = 203007;
    // 订单核销失败
    #[Message('result.order.verify_failed')]
    case ORDER_VERIFY_FAILED = 203008;
    // BANK_BILL_TEMPLATE_NOT_EXIST
    #[Message('result.order.bank_bill_template_not_exist')]
    case ORDER_BANK_BILL_TEMPLATE_NOT_EXIST = 203009;
    // ORDER_BANK_BILL_TEMPLATE_RUNTIME_ERROR
    #[Message('result.order.bank_bill_template_runtime_error')]
    case ORDER_BANK_BILL_TEMPLATE_RUNTIME_ERROR = 203010;
    // TENANT_NOT_OPEN_RECEIPT
    #[Message('result.order.tenant_not_open_receipt')]
    case ORDER_TENANT_NOT_OPEN_RECEIPT = 203011;
    // ORDER_TENANT_NOT_OPEN_PAYMENT
    #[Message('result.order.tenant_not_open_payment')]
    case ORDER_TENANT_NOT_OPEN_PAYMENT = 203012;

    // 交易凭证
    #[Message('result.transaction.trade_voucher_not_exist')]
    case TRANSACTION_VOUCHER_NOT_EXIST = 204001;
}
