
# collection_order_create
## 请求参数
```json
[
  {
    "key": "tenant_id",
    "value": "000001",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "3ac55f62771048",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "租户ID",
    "file_base64": "",
    "content_type": "application/json"
  },
  {
    "key": "app_key",
    "value": "0cb3bd11ae",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "3ac5631fb71049",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "租户应用KEY",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "tenant_order_no",
    "value": "test250910002",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "3acd4b9f771051",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "租户订单号",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "amount",
    "value": "35.44",
    "schema": {
      "type": "number"
    },
    "not_null": 1,
    "param_id": "3acd5b2d371053",
    "file_name": "",
    "field_type": "Number",
    "is_checked": 1,
    "description": "订单金额",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "notify_url",
    "value": "",
    "schema": {
      "type": "string"
    },
    "not_null": -1,
    "param_id": "19e481ba771277",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "通知回调地址",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "timestamp",
    "value": "1757493019",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "cf9cc4371002",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "时间戳",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "sign",
    "value": "786d07b934fbd3934e0c7f8f5a8fe64b",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "cfeef2b7102e",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "签名",
    "file_base64": "",
    "content_type": ""
  }
]
```
## 响应参数
```json
[
    {
        "key": "request_id",
        "value": "df06c355-e7a7-4dc5-b5fc-aa1307598b31",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880b9fdc000",
        "field_type": "String",
        "is_checked": 1,
        "description": "请求ID"
    },
    {
        "key": "path",
        "value": "/v1/api/collection/create_order",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc001",
        "field_type": "String",
        "is_checked": 1,
        "description": "路由"
    },
    {
        "key": "success",
        "value": "true",
        "schema": {
            "type": "boolean"
        },
        "not_null": 1,
        "param_id": "d880ba3dc002",
        "field_type": "Boolean",
        "is_checked": 1,
        "description": "状态"
    },
    {
        "key": "code",
        "value": "200",
        "schema": {
            "type": "number"
        },
        "not_null": 1,
        "param_id": "d880ba3dc003",
        "field_type": "Number",
        "is_checked": 1,
        "description": "错误码"
    },
    {
        "key": "message",
        "value": "成功",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc004",
        "field_type": "String",
        "is_checked": 1,
        "description": "描述信息"
    },
    {
        "key": "data",
        "value": "",
        "schema": {
            "type": "array"
        },
        "not_null": -1,
        "param_id": "d880ba3dc005",
        "field_type": "Array",
        "is_checked": 1,
        "description": "内容"
    },
    {
        "key": "data.platform_order_no",
        "value": "CO2025091008304285543386B91",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc006",
        "field_type": "String",
        "is_checked": 1,
        "description": "平台订单"
    },
    {
        "key": "data.tenant_order_no",
        "value": "test250910002",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc007",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户订单"
    },
    {
        "key": "data.amount",
        "value": "35.44",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc008",
        "field_type": "String",
        "is_checked": 1,
        "description": "订单"
    },
    {
        "key": "data.payable_amount",
        "value": "34.53",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc009",
        "field_type": "String",
        "is_checked": 1,
        "description": "订单应付金额"
    },
    {
        "key": "data.status",
        "value": "10",
        "schema": {
            "type": "number"
        },
        "not_null": 1,
        "param_id": "d880ba3dc00a",
        "field_type": "Number",
        "is_checked": 1,
        "description": "订单状态"
    },
    {
        "key": "data.payer_upi",
        "value": "444@google.com",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc00b",
        "field_type": "String",
        "is_checked": 1,
        "description": "收款UPI账号"
    },
    {
        "key": "data.pay_time",
        "value": "",
        "schema": {
            "type": "null"
        },
        "not_null": 1,
        "param_id": "d880ba3dc00c",
        "field_type": "null",
        "is_checked": 1,
        "description": "支付"
    },
    {
        "key": "data.expire_time",
        "value": "2025-09-10T09:00:42.000000Z",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc00d",
        "field_type": "String",
        "is_checked": 1,
        "description": "失效时间"
    },
    {
        "key": "data.return_url",
        "value": "",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc00e",
        "field_type": "String",
        "is_checked": 1,
        "description": "支付完成后返回地址"
    },
    {
        "key": "data.created_at",
        "value": "2025-09-10T08:30:42.000000Z",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc00f",
        "field_type": "String",
        "is_checked": 1,
        "description": "创建"
    },
    {
        "key": "data.pay_url",
        "value": "/payment/QrBaseDefault/CO2025091008304285543386B91",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc010",
        "field_type": "String",
        "is_checked": 1,
        "description": "收银台"
    },
    {
        "key": "data.meta",
        "value": "",
        "schema": {
            "type": "object"
        },
        "not_null": 1,
        "param_id": "d880ba3dc011",
        "field_type": "Object",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data.meta.paytm",
        "value": "paytmmp://cash_wallet?pa=444@google.com&pn=444&tr=CO2025091008304285543386B91&tn=0000r&am=34.53&cu=INR&mc=5641&url=&mode=02&purpose=00&orgid=159002&sign=MEYCIQCHBg/RU0nnqGczGT+3qmufIH0d4syWKuN/93J8Of+pVwIhAJRHuz0ouV+LC1+MLU9is5mIfphzIYAnLb9yRKM7lXA+&featuretype=money_transfer",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc012",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data.meta.upi",
        "value": "upi://pay?pa=444@google.com&pn=Payment To 444&am=34.53&tn=0000r&cu=INR&tr=CO2025091008304285543386B91",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc013",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data.meta.gpay",
        "value": "gpay://pay?pa=444@google.com&pn=444&tr=CO2025091008304285543386B91&am=34.53&tn=0000r&cu=INR&mc=5641",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc014",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data.meta.phonepe",
        "value": "phonepe://pay?pa=444@google.com&pn=444&tr=CO2025091008304285543386B91&tn=0000r&am=34.53&cu=INR&mc=5641&url=&mode=02&purpose=00&orgid=159002&sign=MEYCIQCHBg/RU0nnqGczGT+3qmufIH0d4syWKuN/93J8Of+pVwIhAJRHuz0ouV+LC1+MLU9is5mIfphzIYAnLb9yRKM7lXA+",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "d880ba3dc015",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    }
]
```

# disbursement_order_create
## 请求参数
```json
[
  {
    "key": "tenant_id",
    "value": "000001",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "3ac55f62771048",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "租户ID",
    "file_base64": "",
    "content_type": "application/json"
  },
  {
    "key": "app_key",
    "value": "0cb3bd11ae",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "3ac5631fb71049",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "租户应用KEY",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "tenant_order_no",
    "value": "test250910002",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "3acd4b9f771051",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "租户订单号",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "amount",
    "value": "22.02",
    "schema": {
      "type": "number"
    },
    "not_null": 1,
    "param_id": "3acd5b2d371053",
    "file_name": "",
    "field_type": "Number",
    "is_checked": 1,
    "description": "订单金额",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "payment_type",
    "value": "2",
    "schema": {
      "type": "integer"
    },
    "not_null": 1,
    "param_id": "111d4b3f38b01a",
    "file_name": "",
    "field_type": "Integer",
    "is_checked": 1,
    "description": "付款类型",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "payee_bank_name",
    "value": "eee4",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "11318acbb8b01e",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "收款人银行名称",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "payee_bank_code",
    "value": "ererwe",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "11319b35b8b021",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "收款人银行编码(ifsc)",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "payee_account_name",
    "value": "eeer",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "1131a80d78b023",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "收款人账户姓名",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "payee_account_no",
    "value": "354534534",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "1131affd78b025",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "收款人银行卡号",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "payee_phone",
    "value": "99676757567",
    "schema": {
      "type": "string"
    },
    "not_null": -1,
    "param_id": "1132010438b02a",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "收款人电话号码",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "payee_upi",
    "value": "rtertre@ddd.com",
    "schema": {
      "type": "string"
    },
    "not_null": 1,
    "param_id": "113215ebb8b02b",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "收款人UPI账号",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "notify_url",
    "value": "http://www.google.com",
    "schema": {
      "type": "string"
    },
    "not_null": -1,
    "param_id": "1133241738b03e",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "回调通知地址",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "notify_remark",
    "value": "notify_remark",
    "schema": {
      "type": "string"
    },
    "not_null": -1,
    "param_id": "11342c8af8b04b",
    "file_name": "",
    "field_type": "String",
    "is_checked": 1,
    "description": "回调通知数据",
    "file_base64": "",
    "content_type": ""
  },
  {
    "key": "timestamp",
    "value": "1757517351",
    "not_null": 1,
    "param_id": "133639271201c",
    "field_type": "Integer",
    "is_checked": 1,
    "description": "时间戳（10位）"
  },
  {
    "key": "sign",
    "value": "426333931d4b29660a5b8a034f7f4c56",
    "not_null": 1,
    "param_id": "133f70277101e",
    "field_type": "String",
    "is_checked": 1,
    "description": "签名"
  }
]
```
## 响应参数
```json
[
    {
        "key": "request_id",
        "type": "Text",
        "value": "9fe49842-49b9-4ed1-978d-a217e0b204fc",
        "not_null": 1,
        "param_id": "13469bef12012",
        "field_type": "String",
        "is_checked": 1,
        "description": "请求ID"
    },
    {
        "key": "path",
        "type": "Text",
        "value": "/v1/api/disbursement/create_order",
        "not_null": 1,
        "param_id": "13469bef12013",
        "field_type": "String",
        "is_checked": 1,
        "description": "路由"
    },
    {
        "key": "success",
        "type": "Text",
        "value": "true",
        "not_null": 1,
        "param_id": "13469bef12014",
        "field_type": "Boolean",
        "is_checked": 1,
        "description": "状态"
    },
    {
        "key": "code",
        "type": "Text",
        "value": "200",
        "not_null": 1,
        "param_id": "13469bef12015",
        "field_type": "Number",
        "is_checked": 1,
        "description": "收款人银行编码(ifsc)"
    },
    {
        "key": "message",
        "type": "Text",
        "value": "成功",
        "not_null": 1,
        "param_id": "13469bef12016",
        "field_type": "String",
        "is_checked": 1,
        "description": "描述信息"
    },
    {
        "key": "data",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "13469bef12017",
        "field_type": "Object",
        "is_checked": 1,
        "description": "内容"
    },
    {
        "key": "data.platform_order_no",
        "type": "Text",
        "value": "DO202509101516019592EB1905E",
        "not_null": 1,
        "param_id": "13469bef12018",
        "field_type": "String",
        "is_checked": 1,
        "description": "平台订单"
    },
    {
        "key": "data.tenant_order_no",
        "type": "Text",
        "value": "test250910002",
        "not_null": 1,
        "param_id": "13469bf312019",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户订单"
    },
    {
        "key": "data.amount",
        "type": "Text",
        "value": "22.02",
        "not_null": 1,
        "param_id": "13469bf31201a",
        "field_type": "Number",
        "is_checked": 1,
        "description": "订单金额"
    },
    {
        "key": "data.status",
        "type": "Text",
        "value": "0",
        "not_null": 1,
        "param_id": "13469bf31201b",
        "field_type": "Number",
        "is_checked": 1,
        "description": "订单状态"
    }
]
```

# collection_order_query
## 请求参数
```json
[
    {
        "key": "tenant_id",
        "value": "000001",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "19e46026b711cb",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户ID",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "app_key",
        "value": "0cb3bd11ae",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "19e4613d7711f6",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "应用Ykey\n",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "platform_order_no",
        "value": "CO2025091008304285543386B91",
        "schema": {
            "type": "string",
            "default": "CO2025072802485176241E758B4"
        },
        "not_null": -1,
        "param_id": "19e46d67f71221",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "平台订单号",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "tenant_order_no",
        "value": "FGGF",
        "schema": {
            "type": "string",
            "default": "FGGF"
        },
        "not_null": -1,
        "param_id": "19e47b8df7124c",
        "file_name": "",
        "field_type": "String",
        "is_checked": -1,
        "description": "租户订单号",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "timestamp",
        "value": "1757516979",
        "not_null": 1,
        "param_id": "132285c371010",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "sign",
        "value": "352277b15f869516715bc6e0fe914a2d",
        "not_null": 1,
        "param_id": "13243cd771011",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    }
]
```
## 响应参数
```json
[
    {
        "key": "request_id",
        "type": "Text",
        "value": "09d40f74-6246-4c05-8341-1fae175e9bbe",
        "not_null": 1,
        "param_id": "1336b85b12000",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "path",
        "type": "Text",
        "value": "/v1/api/collection/query_order",
        "not_null": 1,
        "param_id": "1336b85f12001",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "success",
        "type": "Text",
        "value": "true",
        "not_null": 1,
        "param_id": "1336b85f12002",
        "field_type": "Boolean",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "code",
        "type": "Text",
        "value": "200",
        "not_null": 1,
        "param_id": "1336b85f12003",
        "field_type": "Number",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "message",
        "type": "Text",
        "value": "成功",
        "not_null": 1,
        "param_id": "1336b85f12004",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "1336b85f12005",
        "field_type": "Object",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data.tenant_id",
        "type": "Text",
        "value": "000001",
        "not_null": 1,
        "param_id": "1336b85f12006",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户ID"
    },
    {
        "key": "data.tenant_order_no",
        "type": "Text",
        "value": "test250910002",
        "not_null": 1,
        "param_id": "1336b86312007",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户订单号"
    },
    {
        "key": "data.platform_order_no",
        "type": "Text",
        "value": "CO2025091008304285543386B91",
        "not_null": 1,
        "param_id": "1336b86312008",
        "field_type": "String",
        "is_checked": 1,
        "description": "平台订单号"
    },
    {
        "key": "data.amount",
        "type": "Text",
        "value": "35.44",
        "not_null": 1,
        "param_id": "1336b86312009",
        "field_type": "Number",
        "is_checked": 1,
        "description": "订单金额"
    },
    {
        "key": "data.payable_amount",
        "type": "Text",
        "value": "34.53",
        "not_null": 1,
        "param_id": "1336b8631200a",
        "field_type": "Number",
        "is_checked": 1,
        "description": "应付款金额"
    },
    {
        "key": "data.paid_amount",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "1336b8631200b",
        "field_type": "null",
        "is_checked": 1,
        "description": "实收款金额"
    },
    {
        "key": "data.status",
        "type": "Text",
        "value": "43",
        "not_null": 1,
        "param_id": "1336b8631200c",
        "field_type": "Number",
        "is_checked": 1,
        "description": "订单状态"
    },
    {
        "key": "data.pay_time",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "1336b8631200d",
        "field_type": "null",
        "is_checked": 1,
        "description": "支付时间"
    },
    {
        "key": "data.expire_time",
        "type": "Text",
        "value": "2025-09-10 17:00:42",
        "not_null": 1,
        "param_id": "1336b8631200e",
        "field_type": "String",
        "is_checked": 1,
        "description": "失效时间"
    },
    {
        "key": "data.notify_url",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "1336b8631200f",
        "field_type": "String",
        "is_checked": 1,
        "description": "通知地址"
    },
    {
        "key": "data.notify_status",
        "type": "Text",
        "value": "0",
        "not_null": 1,
        "param_id": "1336b86312010",
        "field_type": "Number",
        "is_checked": 1,
        "description": "通知状态"
    },
    {
        "key": "data.created_at",
        "type": "Text",
        "value": "2025-09-10 16:30:42",
        "not_null": 1,
        "param_id": "1336b86712011",
        "field_type": "String",
        "is_checked": 1,
        "description": "创建时间"
    }
]
```

# disbursement_order_query
## 请求参数
```json
[
    {
        "key": "tenant_id",
        "value": "000001",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "19e46026b711cb",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户ID",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "app_key",
        "value": "0cb3bd11ae",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "19e4613d7711f6",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "应用Ykey\n",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "platform_order_no",
        "value": "DO202509101516019592EB1905E",
        "schema": {
            "type": "string",
            "default": "CO2025072802485176241E758B4"
        },
        "not_null": -1,
        "param_id": "19e46d67f71221",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "平台订单号",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "tenant_order_no",
        "value": "FGGF",
        "schema": {
            "type": "string",
            "default": "FGGF"
        },
        "not_null": -1,
        "param_id": "19e47b8df7124c",
        "file_name": "",
        "field_type": "String",
        "is_checked": -1,
        "description": "租户订单号",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "timestamp",
        "value": "1757517651",
        "not_null": 1,
        "param_id": "135199837102c",
        "field_type": "String",
        "is_checked": 1,
        "description": "时间戳（10位）"
    },
    {
        "key": "sign",
        "value": "b9761f780752714f910bc4ca372da8e4",
        "not_null": 1,
        "param_id": "1351fa2b7102d",
        "field_type": "String",
        "is_checked": 1,
        "description": "签名"
    }
]
```
## 响应参数
```json
[
    {
        "key": "request_id",
        "type": "Text",
        "value": "fef678b0-c8c6-4a7c-b57e-6596ca62bdc3",
        "not_null": 1,
        "param_id": "135be5571201c",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "path",
        "type": "Text",
        "value": "/v1/api/disbursement/query_order",
        "not_null": 1,
        "param_id": "135be55b1201d",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "success",
        "type": "Text",
        "value": "true",
        "not_null": 1,
        "param_id": "135be55b1201e",
        "field_type": "Boolean",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "code",
        "type": "Text",
        "value": "200",
        "not_null": 1,
        "param_id": "135be55b1201f",
        "field_type": "Number",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "message",
        "type": "Text",
        "value": "成功",
        "not_null": 1,
        "param_id": "135be55b12020",
        "field_type": "String",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "135be55b12021",
        "field_type": "Object",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data.tenant_id",
        "type": "Text",
        "value": "000001",
        "not_null": 1,
        "param_id": "135be55b12022",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户ID"
    },
    {
        "key": "data.tenant_order_no",
        "type": "Text",
        "value": "test250910002",
        "not_null": 1,
        "param_id": "135be55b12023",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户订单号"
    },
    {
        "key": "data.platform_order_no",
        "type": "Text",
        "value": "DO202509101516019592EB1905E",
        "not_null": 1,
        "param_id": "135be55b12024",
        "field_type": "String",
        "is_checked": 1,
        "description": "平台订单号"
    },
    {
        "key": "data.amount",
        "type": "Text",
        "value": "22.02",
        "not_null": 1,
        "param_id": "135be55b12025",
        "field_type": "Number",
        "is_checked": 1,
        "description": "订单金额"
    },
    {
        "key": "data.utr",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "135be55b12026",
        "field_type": "String",
        "is_checked": 1,
        "description": "支付凭证（UTR）"
    },
    {
        "key": "data.status",
        "type": "Text",
        "value": "1",
        "not_null": 1,
        "param_id": "135be55b12027",
        "field_type": "Number",
        "is_checked": 1,
        "description": "订单状态"
    },
    {
        "key": "data.pay_time",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "135be55b12028",
        "field_type": "null",
        "is_checked": 1,
        "description": "支付时间"
    },
    {
        "key": "data.notify_url",
        "type": "Text",
        "value": "http://www.google.com",
        "not_null": 1,
        "param_id": "135be55b12029",
        "field_type": "String",
        "is_checked": 1,
        "description": "通知地址"
    },
    {
        "key": "data.notify_status",
        "type": "Text",
        "value": "0",
        "not_null": 1,
        "param_id": "135be55b1202a",
        "field_type": "Number",
        "is_checked": 1,
        "description": "通知状态"
    },
    {
        "key": "data.created_at",
        "type": "Text",
        "value": "2025-09-10 23:16:01",
        "not_null": 1,
        "param_id": "135be55b1202b",
        "field_type": "String",
        "is_checked": 1,
        "description": "创建时间"
    }
]
```

# submitted_utr
## 请求参数
```json
[
    {
        "key": "tenant_id",
        "value": "000001",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "3ac55f62771048",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户ID",
        "file_base64": "",
        "content_type": "application/json"
    },
    {
        "key": "app_key",
        "value": "0cb3bd11ae",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "3ac5631fb71049",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户应用KEY",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "platform_order_no",
        "value": "CO2025091008304285543386B91",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "3acd4b9f771051",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "平台订单号",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "customer_submitted_utr",
        "value": "234324234",
        "not_null": 1,
        "param_id": "135e86cf71038",
        "field_type": "String",
        "is_checked": 1,
        "description": "UTR"
    },
    {
        "key": "timestamp",
        "value": "1757518534",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "cf9cc4371002",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "时间戳",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "sign",
        "value": "0266b769523169ac6b675599da5f4ee3",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "cfeef2b7102e",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "签名",
        "file_base64": "",
        "content_type": ""
    }
]
```
## 响应参数
```json
[
    {
        "key": "request_id",
        "type": "Text",
        "value": "c0d048b6-d508-497e-b54c-c41eec3871c7",
        "not_null": 1,
        "param_id": "13918bfb1202c",
        "field_type": "String",
        "is_checked": 1,
        "description": "请求ID"
    },
    {
        "key": "path",
        "type": "Text",
        "value": "/v1/api/collection/submitted_utr",
        "not_null": 1,
        "param_id": "13918bfb1202d",
        "field_type": "String",
        "is_checked": 1,
        "description": "路由"
    },
    {
        "key": "success",
        "type": "Text",
        "value": "true",
        "not_null": 1,
        "param_id": "13918bfb1202e",
        "field_type": "Boolean",
        "is_checked": 1,
        "description": "状态"
    },
    {
        "key": "code",
        "type": "Text",
        "value": "200",
        "not_null": 1,
        "param_id": "13918bfb1202f",
        "field_type": "Number",
        "is_checked": 1,
        "description": "错误码"
    },
    {
        "key": "message",
        "type": "Text",
        "value": "成功",
        "not_null": 1,
        "param_id": "13918bfb12030",
        "field_type": "String",
        "is_checked": 1,
        "description": "描述信息"
    }
]
```

# query_balance
## 请求参数
```json
[
    {
        "key": "tenant_id",
        "value": "000001",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "3ac55f62771048",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户ID",
        "file_base64": "",
        "content_type": "application/json"
    },
    {
        "key": "app_key",
        "value": "0cb3bd11ae",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "3ac5631fb71049",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户应用KEY",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "timestamp",
        "value": "1757519600",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "cf9cc4371002",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "时间戳",
        "file_base64": "",
        "content_type": ""
    },
    {
        "key": "sign",
        "value": "73cb53684f49e9e4cb54c4310f62cff8",
        "schema": {
            "type": "string"
        },
        "not_null": 1,
        "param_id": "cfeef2b7102e",
        "file_name": "",
        "field_type": "String",
        "is_checked": 1,
        "description": "签名",
        "file_base64": "",
        "content_type": ""
    }
]
```
## 响应参数
```json
[
    {
        "key": "request_id",
        "type": "Text",
        "value": "77d180b1-9df3-4fcb-87dc-6c9c44d1a5c4",
        "not_null": 1,
        "param_id": "13cf3cd712031",
        "field_type": "String",
        "is_checked": 1,
        "description": "请求ID"
    },
    {
        "key": "path",
        "type": "Text",
        "value": "/v1/api/tenant/query_balance",
        "not_null": 1,
        "param_id": "13cf3cd712032",
        "field_type": "String",
        "is_checked": 1,
        "description": "路由"
    },
    {
        "key": "success",
        "type": "Text",
        "value": "true",
        "not_null": 1,
        "param_id": "13cf3cd712033",
        "field_type": "Boolean",
        "is_checked": 1,
        "description": "状态"
    },
    {
        "key": "code",
        "type": "Text",
        "value": "200",
        "not_null": 1,
        "param_id": "13cf3cd712034",
        "field_type": "Number",
        "is_checked": 1,
        "description": "错误码"
    },
    {
        "key": "message",
        "type": "Text",
        "value": "成功",
        "not_null": 1,
        "param_id": "13cf3cd712035",
        "field_type": "String",
        "is_checked": 1,
        "description": "描述信息"
    },
    {
        "key": "data",
        "type": "Text",
        "value": "",
        "not_null": 1,
        "param_id": "13cf3cd712036",
        "field_type": "Array",
        "is_checked": 1,
        "description": ""
    },
    {
        "key": "data.tenant_id",
        "type": "Text",
        "value": "000001",
        "not_null": 1,
        "param_id": "13cf3cd712037",
        "field_type": "String",
        "is_checked": 1,
        "description": "租户ID"
    },
    {
        "key": "data.account_id",
        "type": "Text",
        "value": "AC000001",
        "not_null": 1,
        "param_id": "13cf3cd712038",
        "field_type": "String",
        "is_checked": 1,
        "description": "账户ID"
    },
    {
        "key": "data.balance_available",
        "type": "Text",
        "value": "1426.30",
        "not_null": 1,
        "param_id": "13cf3cd712039",
        "field_type": "String",
        "is_checked": 1,
        "description": "可用余额"
    },
    {
        "key": "data.balance_frozen",
        "type": "Text",
        "value": "2.00",
        "not_null": 1,
        "param_id": "13cf3cd71203a",
        "field_type": "String",
        "is_checked": 1,
        "description": "冻结"
    },
    {
        "key": "data.account_type",
        "type": "Text",
        "value": "1",
        "not_null": 1,
        "param_id": "13cf3cd71203b",
        "field_type": "Number",
        "is_checked": 1,
        "description": "账户类型:1-收款账户 2-付款账户"
    },
    {
        "key": "data.updated_at",
        "type": "Text",
        "value": "2025-08-30 16:01:40",
        "not_null": 1,
        "param_id": "13cf3cd71203c",
        "field_type": "String",
        "is_checked": 1,
        "description": "更新"
    }
]
```
