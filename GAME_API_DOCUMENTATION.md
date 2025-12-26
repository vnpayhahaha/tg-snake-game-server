# Telegram贪吃蛇游戏 - API接口文档

## 文档说明

本文档详细描述了Telegram贪吃蛇游戏系统的所有后台管理API接口。

**基础信息：**
- 基础路径：`/admin/tg_game`
- 请求方式：RESTful API
- 认证方式：JWT Token
- 返回格式：JSON

**通用响应格式：**
```json
{
  "code": 200,
  "message": "success",
  "data": {}
}
```

**状态码说明：**
- `200` - 成功
- `400` - 请求参数错误
- `401` - 未授权
- `403` - 权限不足
- `404` - 资源不存在
- `422` - 验证失败
- `500` - 服务器错误

---

## 目录

1. [游戏群组管理](#1-游戏群组管理)
2. [群组配置管理](#2-群组配置管理)
3. [配置变更日志](#3-配置变更日志)
4. [玩家钱包绑定](#4-玩家钱包绑定)
5. [钱包绑定日志](#5-钱包绑定日志)
6. [蛇身节点管理](#6-蛇身节点管理)
7. [中奖记录管理](#7-中奖记录管理)
8. [中奖转账管理](#8-中奖转账管理)
9. [派奖队列管理](#9-派奖队列管理)
10. [TRON交易日志](#10-tron交易日志)

---

## 1. 游戏群组管理

### 1.1 获取游戏群组列表

**接口地址：** `GET /admin/tg_game/group/list`

**权限代码：** `tg_game:group:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| config_id | integer | 否 | 配置ID |
| tg_chat_id | integer | 否 | Telegram群组ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 50,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "config_id": 1,
        "tg_chat_id": -1001234567890,
        "prize_pool_amount": 1000.50,
        "current_snake_nodes": "1,2,3,4,5",
        "last_prize_amount": 500.00,
        "last_prize_at": "2025-01-08 10:30:00",
        "version": 5,
        "created_at": "2025-01-01 00:00:00",
        "updated_at": "2025-01-08 10:30:00"
      }
    ]
  }
}
```

### 1.2 获取群组详情

**接口地址：** `GET /admin/tg_game/group/{id}`

**权限代码：** `tg_game:group:detail`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 群组ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "config_id": 1,
    "tg_chat_id": -1001234567890,
    "prize_pool_amount": 1000.50,
    "current_snake_nodes": "1,2,3,4,5",
    "last_snake_nodes": "10,11,12,13",
    "last_prize_nodes": "10,11,12,13",
    "last_prize_amount": 500.00,
    "last_prize_address": "TXxx...xxx,TYyy...yyy",
    "last_prize_serial_no": "WIN-1-20250108103000",
    "last_prize_at": "2025-01-08 10:30:00",
    "version": 5,
    "created_at": "2025-01-01 00:00:00",
    "updated_at": "2025-01-08 10:30:00"
  }
}
```

### 1.3 获取群组统计数据

**接口地址：** `GET /admin/tg_game/group/{id}/statistics`

**权限代码：** `tg_game:group:statistics`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 群组ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| date_start | string | 否 | 开始日期 (YYYY-MM-DD) |
| date_end | string | 否 | 结束日期 (YYYY-MM-DD) |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total_nodes": 150,
    "total_bet_amount": 15000.00,
    "total_prizes": 5,
    "total_prize_amount": 7500.00,
    "current_pool_amount": 1000.50,
    "active_players": 45
  }
}
```

### 1.4 创建游戏群组

**接口地址：** `POST /admin/tg_game/group`

**权限代码：** `tg_game:group:create`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| config_id | integer | 是 | 配置ID |
| group_name | string | 是 | 群组名称，最长100字符 |
| status | integer | 是 | 状态：1-正常，2-停用 |

**请求示例：**
```json
{
  "config_id": 1,
  "group_name": "测试游戏群组",
  "status": 1
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

### 1.5 更新游戏群组

**接口地址：** `PUT /admin/tg_game/group/{id}`

**权限代码：** `tg_game:group:update`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 群组ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| group_name | string | 否 | 群组名称 |
| status | integer | 否 | 状态：1-正常，2-停用 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

### 1.6 重置群组奖池

**接口地址：** `POST /admin/tg_game/group/{id}/reset_prize_pool`

**权限代码：** `tg_game:group:resetPrizePool`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 群组ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

### 1.7 删除游戏群组

**接口地址：** `DELETE /admin/tg_game/group`

**权限代码：** `tg_game:group:delete`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| ids | array | 是 | 群组ID数组 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

---

## 2. 群组配置管理

### 2.1 获取群组配置列表

**接口地址：** `GET /admin/tg_game/config/list`

**权限代码：** `tg_game:config:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| tenant_id | string | 否 | 租户ID |
| tg_chat_id | integer | 否 | Telegram群组ID |
| status | integer | 否 | 状态筛选 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 20,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "tenant_id": "TENANT001",
        "tg_chat_id": -1001234567890,
        "tg_chat_title": "贪吃蛇游戏群",
        "wallet_address": "TXxx...xxxxx",
        "wallet_change_count": 2,
        "hot_wallet_address": "TYyy...yyyyy",
        "bet_amount": 100.00,
        "platform_fee_rate": 0.10,
        "telegram_admin_whitelist": "123456,789012",
        "status": 1,
        "created_at": "2025-01-01 00:00:00",
        "updated_at": "2025-01-08 10:00:00"
      }
    ]
  }
}
```

### 2.2 获取配置详情

**接口地址：** `GET /admin/tg_game/config/{id}`

**权限代码：** `tg_game:config:detail`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 配置ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "tenant_id": "TENANT001",
    "tg_chat_id": -1001234567890,
    "tg_chat_title": "贪吃蛇游戏群",
    "wallet_address": "TXxx...xxxxx",
    "wallet_change_count": 2,
    "pending_wallet_address": null,
    "wallet_change_status": 1,
    "wallet_change_start_at": null,
    "wallet_change_end_at": null,
    "hot_wallet_address": "TYyy...yyyyy",
    "hot_wallet_private_key": "***encrypted***",
    "bet_amount": 100.00,
    "platform_fee_rate": 0.10,
    "telegram_admin_whitelist": "123456,789012",
    "status": 1,
    "created_at": "2025-01-01 00:00:00",
    "updated_at": "2025-01-08 10:00:00"
  }
}
```

### 2.3 创建群组配置

**接口地址：** `POST /admin/tg_game/config`

**权限代码：** `tg_game:config:create`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| tenant_id | string | 是 | 租户ID，最长50字符 |
| tg_chat_id | integer | 是 | Telegram群组ID |
| tg_group_name | string | 是 | 群组名称，最长200字符 |
| wallet_address | string | 是 | TRON钱包地址 |
| min_bet_amount | number | 是 | 最小投注金额 |
| snake_head_ticket | string | 是 | 蛇头票号（00-99） |
| prize_match_count | integer | 是 | 中奖匹配数量 |
| prize_ratio_jackpot | number | 是 | Jackpot奖金比例(0-100) |
| prize_ratio_range_match | number | 是 | 范围匹配奖金比例(0-100) |
| prize_ratio_platform | number | 是 | 平台手续费比例(0-100) |
| status | integer | 是 | 状态：1-正常，2-停用 |

**请求示例：**
```json
{
  "tenant_id": "TENANT001",
  "tg_chat_id": -1001234567890,
  "tg_group_name": "贪吃蛇游戏群",
  "wallet_address": "TXxx...xxxxx",
  "min_bet_amount": 100.00,
  "snake_head_ticket": "88",
  "prize_match_count": 3,
  "prize_ratio_jackpot": 50.0,
  "prize_ratio_range_match": 40.0,
  "prize_ratio_platform": 10.0,
  "status": 1
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

### 2.4 开始钱包变更

**接口地址：** `POST /admin/tg_game/config/{id}/start_wallet_change`

**权限代码：** `tg_game:config:startWalletChange`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 配置ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| new_wallet_address | string | 是 | 新钱包地址 |
| cooldown_minutes | integer | 是 | 冷却时间（分钟），1-1440 |

**请求示例：**
```json
{
  "new_wallet_address": "TZzz...zzzzz",
  "cooldown_minutes": 60
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "wallet_change_start_at": "2025-01-08 10:00:00",
    "wallet_change_end_at": "2025-01-08 11:00:00"
  }
}
```

### 2.5 取消钱包变更

**接口地址：** `POST /admin/tg_game/config/{id}/cancel_wallet_change`

**权限代码：** `tg_game:config:cancelWalletChange`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 配置ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

### 2.6 完成钱包变更

**接口地址：** `POST /admin/tg_game/config/{id}/complete_wallet_change`

**权限代码：** `tg_game:config:completeWalletChange`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 配置ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "old_wallet": "TXxx...xxxxx",
    "new_wallet": "TZzz...zzzzz",
    "wallet_cycle": 3
  }
}
```

---

## 3. 配置变更日志

### 3.1 获取配置变更日志列表

**接口地址：** `GET /admin/tg_game/config_log/list`

**权限代码：** `tg_game:config_log:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| config_id | integer | 否 | 配置ID |
| action | string | 否 | 操作类型 |
| source | integer | 否 | 来源：1-管理后台，2-Telegram Bot，3-系统 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 100,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "config_id": 1,
        "action": "update_wallet",
        "source": 1,
        "old_data": "{\"wallet_address\":\"TXxx...xxxxx\"}",
        "new_data": "{\"wallet_address\":\"TYyy...yyyyy\"}",
        "operator_id": 1,
        "created_at": "2025-01-08 10:00:00"
      }
    ]
  }
}
```

### 3.2 根据配置ID查询变更历史

**接口地址：** `GET /admin/tg_game/config_log/by_config/{configId}`

**权限代码：** `tg_game:config_log:byConfig`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| configId | integer | 是 | 配置ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| limit | integer | 否 | 数量限制，默认50 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "config_id": 1,
      "action": "update_wallet",
      "source": 1,
      "old_data": "{\"wallet_address\":\"TXxx...xxxxx\"}",
      "new_data": "{\"wallet_address\":\"TYyy...yyyyy\"}",
      "operator_id": 1,
      "created_at": "2025-01-08 10:00:00"
    }
  ]
}
```

### 3.3 获取钱包变更历史

**接口地址：** `GET /admin/tg_game/config_log/wallet_changes/{configId}`

**权限代码：** `tg_game:config_log:walletChanges`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| configId | integer | 是 | 配置ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "config_id": 1,
      "action": "wallet_change_start",
      "old_wallet": "TXxx...xxxxx",
      "new_wallet": "TYyy...yyyyy",
      "wallet_cycle": 2,
      "created_at": "2025-01-08 10:00:00"
    }
  ]
}
```

---

## 4. 玩家钱包绑定

### 4.1 获取玩家钱包绑定列表

**接口地址：** `GET /admin/tg_game/wallet_binding/list`

**权限代码：** `tg_game:wallet_binding:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| group_id | integer | 否 | 群组ID |
| tg_user_id | integer | 否 | Telegram用户ID |
| wallet_address | string | 否 | 钱包地址 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 200,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "group_id": 1,
        "tg_user_id": 123456,
        "tg_username": "john_doe",
        "tg_first_name": "John",
        "tg_last_name": "Doe",
        "wallet_address": "TXxx...xxxxx",
        "bind_at": "2025-01-05 12:00:00",
        "created_at": "2025-01-05 12:00:00",
        "updated_at": "2025-01-05 12:00:00"
      }
    ]
  }
}
```

### 4.2 根据Telegram用户ID查询绑定

**接口地址：** `GET /admin/tg_game/wallet_binding/by_tg_user/{groupId}/{tgUserId}`

**权限代码：** `tg_game:wallet_binding:byTgUser`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |
| tgUserId | integer | 是 | Telegram用户ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "group_id": 1,
    "tg_user_id": 123456,
    "tg_username": "john_doe",
    "tg_first_name": "John",
    "tg_last_name": "Doe",
    "wallet_address": "TXxx...xxxxx",
    "bind_at": "2025-01-05 12:00:00",
    "created_at": "2025-01-05 12:00:00",
    "updated_at": "2025-01-05 12:00:00"
  }
}
```

### 4.3 创建钱包绑定

**接口地址：** `POST /admin/tg_game/wallet_binding`

**权限代码：** `tg_game:wallet_binding:create`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| group_id | integer | 是 | 群组ID |
| tg_user_id | integer | 是 | Telegram用户ID |
| tg_username | string | 否 | Telegram用户名 |
| wallet_address | string | 是 | 钱包地址 |

**请求示例：**
```json
{
  "group_id": 1,
  "tg_user_id": 123456,
  "tg_username": "john_doe",
  "wallet_address": "TXxx...xxxxx"
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "binding_id": 1,
    "success": true
  }
}
```

### 4.4 批量导入绑定关系

**接口地址：** `POST /admin/tg_game/wallet_binding/batch_import`

**权限代码：** `tg_game:wallet_binding:batchImport`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| group_id | integer | 是 | 群组ID |
| bindings | array | 是 | 绑定数据数组 |
| bindings[].tg_user_id | integer | 是 | Telegram用户ID |
| bindings[].tg_username | string | 否 | Telegram用户名 |
| bindings[].wallet_address | string | 是 | 钱包地址 |

**请求示例：**
```json
{
  "group_id": 1,
  "bindings": [
    {
      "tg_user_id": 123456,
      "tg_username": "john_doe",
      "wallet_address": "TXxx...xxxxx"
    },
    {
      "tg_user_id": 789012,
      "tg_username": "jane_doe",
      "wallet_address": "TYyy...yyyyy"
    }
  ]
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 2,
    "success": 2,
    "failed": 0,
    "errors": []
  }
}
```

### 4.5 解除绑定

**接口地址：** `POST /admin/tg_game/wallet_binding/{id}/unbind`

**权限代码：** `tg_game:wallet_binding:unbind`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 绑定记录ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

---

## 5. 钱包绑定日志

### 5.1 获取钱包绑定日志列表

**接口地址：** `GET /admin/tg_game/wallet_binding_log/list`

**权限代码：** `tg_game:wallet_binding_log:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| group_id | integer | 否 | 群组ID |
| tg_user_id | integer | 否 | Telegram用户ID |
| action | string | 否 | 操作类型 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 50,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "group_id": 1,
        "tg_user_id": 123456,
        "tg_username": "john_doe",
        "action": "bind",
        "old_wallet_address": null,
        "new_wallet_address": "TXxx...xxxxx",
        "source": 2,
        "created_at": "2025-01-05 12:00:00"
      }
    ]
  }
}
```

### 5.2 根据用户查询绑定历史

**接口地址：** `GET /admin/tg_game/wallet_binding_log/by_user/{groupId}/{tgUserId}`

**权限代码：** `tg_game:wallet_binding_log:byUser`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |
| tgUserId | integer | 是 | Telegram用户ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| limit | integer | 否 | 数量限制，默认50 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "group_id": 1,
      "tg_user_id": 123456,
      "tg_username": "john_doe",
      "action": "bind",
      "old_wallet_address": null,
      "new_wallet_address": "TXxx...xxxxx",
      "source": 2,
      "created_at": "2025-01-05 12:00:00"
    }
  ]
}
```

---

## 6. 蛇身节点管理

### 6.1 获取蛇身节点列表

**接口地址：** `GET /admin/tg_game/snake_node/list`

**权限代码：** `tg_game:snake_node:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| group_id | integer | 否 | 群组ID |
| wallet_cycle | integer | 否 | 钱包周期 |
| ticket_number | string | 否 | 购彩凭证 |
| player_address | string | 否 | 玩家钱包地址 |
| status | integer | 否 | 状态 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 500,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "group_id": 1,
        "wallet_cycle": 2,
        "ticket_number": "88",
        "ticket_serial_no": "20250108-001",
        "player_address": "TXxx...xxxxx",
        "player_tg_username": "john_doe",
        "player_tg_user_id": 123456,
        "amount": 100.00,
        "tx_hash": "abc123...",
        "block_height": 12345678,
        "daily_sequence": 1,
        "status": 1,
        "matched_prize_id": null,
        "created_at": "2025-01-08 10:00:00"
      }
    ]
  }
}
```

### 6.2 获取群组活跃节点

**接口地址：** `GET /admin/tg_game/snake_node/active/{groupId}`

**权限代码：** `tg_game:snake_node:active`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "group_id": 1,
      "ticket_number": "88",
      "ticket_serial_no": "20250108-001",
      "player_address": "TXxx...xxxxx",
      "amount": 100.00,
      "status": 1,
      "created_at": "2025-01-08 10:00:00"
    }
  ]
}
```

### 6.3 根据钱包周期查询节点

**接口地址：** `GET /admin/tg_game/snake_node/by_wallet_cycle/{groupId}/{walletCycle}`

**权限代码：** `tg_game:snake_node:byWalletCycle`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |
| walletCycle | integer | 是 | 钱包周期 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "wallet_cycle": 2,
      "ticket_number": "88",
      "player_address": "TXxx...xxxxx",
      "amount": 100.00,
      "status": 1,
      "created_at": "2025-01-08 10:00:00"
    }
  ]
}
```

### 6.4 根据玩家查询购彩记录

**接口地址：** `GET /admin/tg_game/snake_node/by_player/{groupId}/{walletAddress}`

**权限代码：** `tg_game:snake_node:byPlayer`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |
| walletAddress | string | 是 | 钱包地址 |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| limit | integer | 否 | 数量限制，默认50 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "ticket_number": "88",
      "ticket_serial_no": "20250108-001",
      "amount": 100.00,
      "status": 1,
      "matched_prize_id": null,
      "created_at": "2025-01-08 10:00:00"
    }
  ]
}
```

### 6.5 根据交易哈希查询节点

**接口地址：** `GET /admin/tg_game/snake_node/by_tx_hash/{txHash}`

**权限代码：** `tg_game:snake_node:byTxHash`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| txHash | string | 是 | 交易哈希 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "group_id": 1,
    "ticket_number": "88",
    "player_address": "TXxx...xxxxx",
    "amount": 100.00,
    "tx_hash": "abc123...",
    "block_height": 12345678,
    "status": 1,
    "created_at": "2025-01-08 10:00:00"
  }
}
```

### 6.6 获取当日节点统计

**接口地址：** `GET /admin/tg_game/snake_node/daily_statistics/{groupId}`

**权限代码：** `tg_game:snake_node:dailyStatistics`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| date | string | 否 | 日期(YYYY-MM-DD)，默认今天 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total_nodes": 150,
    "total_amount": 15000.00,
    "unique_players": 45,
    "active_nodes": 120,
    "matched_nodes": 30
  }
}
```

### 6.7 手动归档节点

**接口地址：** `POST /admin/tg_game/snake_node/{id}/archive`

**权限代码：** `tg_game:snake_node:archive`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 节点ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

### 6.8 更新节点状态

**接口地址：** `PUT /admin/tg_game/snake_node/{id}/status`

**权限代码：** `tg_game:snake_node:updateStatus`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 节点ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| status | integer | 是 | 状态：1-活跃，2-已中奖，3-未中奖 |

**请求示例：**
```json
{
  "status": 2
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

---

## 7. 中奖记录管理

### 7.1 获取中奖记录列表

**接口地址：** `GET /admin/tg_game/prize/list`

**权限代码：** `tg_game:prize:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| group_id | integer | 否 | 群组ID |
| prize_serial_no | string | 否 | 开奖流水号 |
| status | integer | 否 | 状态 |
| wallet_cycle | integer | 否 | 钱包周期 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 100,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "group_id": 1,
        "prize_serial_no": "WIN-1-20250108103000",
        "wallet_cycle": 2,
        "ticket_number": "88",
        "winner_node_id_first": 10,
        "winner_node_id_last": 20,
        "winner_node_ids": "10,11,12,13,14,15,16,17,18,19,20",
        "total_amount": 1100.00,
        "platform_fee": 110.00,
        "fee_rate": 0.10,
        "prize_pool": 5000.00,
        "prize_amount": 4890.00,
        "prize_per_winner": 444.55,
        "pool_remaining": 110.00,
        "winner_count": 11,
        "status": 3,
        "version": 1,
        "created_at": "2025-01-08 10:30:00",
        "updated_at": "2025-01-08 11:00:00"
      }
    ]
  }
}
```

### 7.2 获取中奖记录详情

**接口地址：** `GET /admin/tg_game/prize/{id}`

**权限代码：** `tg_game:prize:detail`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 中奖记录ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "group_id": 1,
    "prize_serial_no": "WIN-1-20250108103000",
    "wallet_cycle": 2,
    "ticket_number": "88",
    "winner_node_id_first": 10,
    "winner_node_id_last": 20,
    "winner_node_ids": "10,11,12,13,14,15,16,17,18,19,20",
    "total_amount": 1100.00,
    "platform_fee": 110.00,
    "fee_rate": 0.10,
    "prize_pool": 5000.00,
    "prize_amount": 4890.00,
    "prize_per_winner": 444.55,
    "pool_remaining": 110.00,
    "winner_count": 11,
    "status": 3,
    "version": 1,
    "created_at": "2025-01-08 10:30:00",
    "updated_at": "2025-01-08 11:00:00"
  }
}
```

### 7.3 根据流水号查询中奖记录

**接口地址：** `GET /admin/tg_game/prize/by_serial/{serialNo}`

**权限代码：** `tg_game:prize:bySerial`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| serialNo | string | 是 | 开奖流水号 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "prize_serial_no": "WIN-1-20250108103000",
    "group_id": 1,
    "prize_amount": 4890.00,
    "winner_count": 11,
    "status": 3,
    "created_at": "2025-01-08 10:30:00"
  }
}
```

### 7.4 获取群组中奖记录

**接口地址：** `GET /admin/tg_game/prize/by_group/{groupId}`

**权限代码：** `tg_game:prize:byGroup`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| limit | integer | 否 | 数量限制，默认20 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "prize_serial_no": "WIN-1-20250108103000",
      "prize_amount": 4890.00,
      "winner_count": 11,
      "status": 3,
      "created_at": "2025-01-08 10:30:00"
    }
  ]
}
```

### 7.5 获取中奖统计

**接口地址：** `GET /admin/tg_game/prize/statistics/{groupId}`

**权限代码：** `tg_game:prize:statistics`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| date_start | string | 否 | 开始日期(YYYY-MM-DD) |
| date_end | string | 否 | 结束日期(YYYY-MM-DD) |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total_prizes": 10,
    "total_prize_amount": 50000.00,
    "total_winners": 110,
    "total_platform_fee": 5000.00,
    "jackpot_count": 2,
    "range_match_count": 8
  }
}
```

### 7.6 获取中奖记录的转账详情

**接口地址：** `GET /admin/tg_game/prize/{id}/transfers`

**权限代码：** `tg_game:prize:transfers`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 中奖记录ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "prize_record_id": 1,
      "node_id": 10,
      "to_address": "TXxx...xxxxx",
      "amount": 444.55,
      "tx_hash": "abc123...",
      "status": 3,
      "created_at": "2025-01-08 10:30:00"
    }
  ]
}
```

### 7.7 手动重新处理中奖派发

**接口地址：** `POST /admin/tg_game/prize/{id}/reprocess`

**权限代码：** `tg_game:prize:reprocess`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 中奖记录ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "success": true,
    "message": "重新处理成功",
    "queued_count": 11
  }
}
```

### 7.8 更新中奖记录状态

**接口地址：** `PUT /admin/tg_game/prize/{id}/status`

**权限代码：** `tg_game:prize:updateStatus`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 中奖记录ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| status | integer | 是 | 状态：1-待处理，2-转账中，3-已完成，4-失败 |

**请求示例：**
```json
{
  "status": 3
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

---

## 8. 中奖转账管理

### 8.1 获取中奖转账列表

**接口地址：** `GET /admin/tg_game/prize_transfer/list`

**权限代码：** `tg_game:prize_transfer:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| prize_record_id | integer | 否 | 中奖记录ID |
| to_address | string | 否 | 收款地址 |
| status | integer | 否 | 状态 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 200,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "prize_record_id": 1,
        "prize_serial_no": "WIN-1-20250108103000",
        "node_id": 10,
        "to_address": "TXxx...xxxxx",
        "amount": 444.55,
        "tx_hash": "abc123...",
        "status": 3,
        "retry_count": 0,
        "error_message": null,
        "created_at": "2025-01-08 10:30:00",
        "updated_at": "2025-01-08 10:35:00"
      }
    ]
  }
}
```

### 8.2 获取待处理的转账

**接口地址：** `GET /admin/tg_game/prize_transfer/pending`

**权限代码：** `tg_game:prize_transfer:pending`

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "prize_record_id": 1,
      "to_address": "TXxx...xxxxx",
      "amount": 444.55,
      "status": 1,
      "created_at": "2025-01-08 10:30:00"
    }
  ]
}
```

### 8.3 获取失败的转账

**接口地址：** `GET /admin/tg_game/prize_transfer/failed`

**权限代码：** `tg_game:prize_transfer:failed`

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 2,
      "prize_record_id": 1,
      "to_address": "TYyy...yyyyy",
      "amount": 444.55,
      "status": 4,
      "retry_count": 3,
      "error_message": "Insufficient balance",
      "created_at": "2025-01-08 10:30:00"
    }
  ]
}
```

### 8.4 根据中奖记录查询转账

**接口地址：** `GET /admin/tg_game/prize_transfer/by_prize/{prizeId}`

**权限代码：** `tg_game:prize_transfer:byPrize`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| prizeId | integer | 是 | 中奖记录ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "node_id": 10,
      "to_address": "TXxx...xxxxx",
      "amount": 444.55,
      "tx_hash": "abc123...",
      "status": 3,
      "created_at": "2025-01-08 10:30:00"
    }
  ]
}
```

### 8.5 根据交易哈希查询转账

**接口地址：** `GET /admin/tg_game/prize_transfer/by_tx_hash/{txHash}`

**权限代码：** `tg_game:prize_transfer:byTxHash`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| txHash | string | 是 | 交易哈希 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "prize_record_id": 1,
    "to_address": "TXxx...xxxxx",
    "amount": 444.55,
    "tx_hash": "abc123...",
    "status": 3,
    "created_at": "2025-01-08 10:30:00"
  }
}
```

### 8.6 手动重试转账

**接口地址：** `POST /admin/tg_game/prize_transfer/{id}/retry`

**权限代码：** `tg_game:prize_transfer:retry`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 转账记录ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "success": true,
    "message": "重试成功"
  }
}
```

### 8.7 批量重试失败转账

**接口地址：** `POST /admin/tg_game/prize_transfer/batch_retry`

**权限代码：** `tg_game:prize_transfer:batchRetry`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| transfer_ids | array | 是 | 转账记录ID数组 |

**请求示例：**
```json
{
  "transfer_ids": [1, 2, 3]
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 3,
    "success": 2,
    "failed": 1,
    "errors": [
      {
        "id": 3,
        "error": "Maximum retry count exceeded"
      }
    ]
  }
}
```

### 8.8 手动标记转账为成功

**接口地址：** `POST /admin/tg_game/prize_transfer/{id}/mark_success`

**权限代码：** `tg_game:prize_transfer:markSuccess`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 转账记录ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| tx_hash | string | 否 | 交易哈希 |

**请求示例：**
```json
{
  "tx_hash": "abc123..."
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

### 8.9 更新转账状态

**接口地址：** `PUT /admin/tg_game/prize_transfer/{id}/status`

**权限代码：** `tg_game:prize_transfer:updateStatus`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 转账记录ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| status | integer | 是 | 状态：1-待处理，2-处理中，3-成功，4-失败 |

**请求示例：**
```json
{
  "status": 3
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

---

## 9. 派奖队列管理

### 9.1 获取派奖队列列表

**接口地址：** `GET /admin/tg_game/dispatch_queue/list`

**权限代码：** `tg_game:dispatch_queue:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| group_id | integer | 否 | 群组ID |
| prize_record_id | integer | 否 | 中奖记录ID |
| status | integer | 否 | 状态 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 150,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "prize_record_id": 1,
        "prize_transfer_id": 1,
        "group_id": 1,
        "prize_serial_no": "WIN-1-20250108103000",
        "priority": 2,
        "status": 3,
        "retry_count": 0,
        "max_retry": 3,
        "task_data": "{\"transfer_id\":1,\"to_address\":\"TXxx...xxxxx\",\"amount\":444.55}",
        "error_message": null,
        "scheduled_at": "2025-01-08 10:30:00",
        "started_at": "2025-01-08 10:30:05",
        "completed_at": "2025-01-08 10:35:00",
        "version": 1,
        "created_at": "2025-01-08 10:30:00"
      }
    ]
  }
}
```

### 9.2 获取待处理派奖队列

**接口地址：** `GET /admin/tg_game/dispatch_queue/pending`

**权限代码：** `tg_game:dispatch_queue:pending`

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "prize_record_id": 1,
      "group_id": 1,
      "prize_serial_no": "WIN-1-20250108103000",
      "status": 1,
      "created_at": "2025-01-08 10:30:00"
    }
  ]
}
```

### 9.3 获取失败派奖队列

**接口地址：** `GET /admin/tg_game/dispatch_queue/failed`

**权限代码：** `tg_game:dispatch_queue:failed`

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 2,
      "prize_record_id": 1,
      "group_id": 1,
      "status": 4,
      "retry_count": 3,
      "error_message": "Transfer failed",
      "created_at": "2025-01-08 10:30:00"
    }
  ]
}
```

### 9.4 根据中奖记录查询派奖队列

**接口地址：** `GET /admin/tg_game/dispatch_queue/by_prize/{prizeId}`

**权限代码：** `tg_game:dispatch_queue:byPrize`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| prizeId | integer | 是 | 中奖记录ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "prize_record_id": 1,
    "prize_transfer_id": 1,
    "status": 3,
    "retry_count": 0,
    "completed_at": "2025-01-08 10:35:00",
    "created_at": "2025-01-08 10:30:00"
  }
}
```

### 9.5 手动重试派发

**接口地址：** `POST /admin/tg_game/dispatch_queue/{id}/retry`

**权限代码：** `tg_game:dispatch_queue:retry`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 派奖队列ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "success": true,
    "message": "重试成功"
  }
}
```

### 9.6 批量重试失败派发

**接口地址：** `POST /admin/tg_game/dispatch_queue/batch_retry`

**权限代码：** `tg_game:dispatch_queue:batchRetry`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| queue_ids | array | 是 | 派奖队列ID数组 |

**请求示例：**
```json
{
  "queue_ids": [1, 2, 3]
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 3,
    "success": 2,
    "failed": 1
  }
}
```

### 9.7 手动标记为成功

**接口地址：** `POST /admin/tg_game/dispatch_queue/{id}/mark_success`

**权限代码：** `tg_game:dispatch_queue:markSuccess`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 派奖队列ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": null
}
```

---

## 10. TRON交易日志

### 10.1 获取交易日志列表

**接口地址：** `GET /admin/tg_game/tron_log/list`

**权限代码：** `tg_game:tron_log:list`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认1 |
| limit | integer | 否 | 每页数量，默认10 |
| group_id | integer | 否 | 群组ID |
| from_address | string | 否 | 发送地址 |
| to_address | string | 否 | 接收地址 |
| is_valid | boolean | 否 | 是否有效 |
| processed | boolean | 否 | 是否已处理 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 500,
    "per_page": 10,
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "group_id": 1,
        "tx_hash": "abc123...",
        "from_address": "TXxx...xxxxx",
        "to_address": "TYyy...yyyyy",
        "amount": 100.00,
        "block_height": 12345678,
        "block_timestamp": 1704700800,
        "status": "success",
        "is_valid": true,
        "invalid_reason": null,
        "processed": true,
        "created_at": "2025-01-08 10:00:00"
      }
    ]
  }
}
```

### 10.2 获取未处理的交易

**接口地址：** `GET /admin/tg_game/tron_log/unprocessed`

**权限代码：** `tg_game:tron_log:unprocessed`

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 2,
      "group_id": 1,
      "tx_hash": "def456...",
      "from_address": "TXxx...xxxxx",
      "to_address": "TYyy...yyyyy",
      "amount": 100.00,
      "is_valid": true,
      "processed": false,
      "created_at": "2025-01-08 11:00:00"
    }
  ]
}
```

### 10.3 根据群组ID查询交易

**接口地址：** `GET /admin/tg_game/tron_log/by_group/{groupId}`

**权限代码：** `tg_game:tron_log:byGroup`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| groupId | integer | 是 | 群组ID |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| limit | integer | 否 | 数量限制，默认100 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "tx_hash": "abc123...",
      "from_address": "TXxx...xxxxx",
      "to_address": "TYyy...yyyyy",
      "amount": 100.00,
      "status": "success",
      "created_at": "2025-01-08 10:00:00"
    }
  ]
}
```

### 10.4 根据交易哈希查询

**接口地址：** `GET /admin/tg_game/tron_log/by_tx_hash/{txHash}`

**权限代码：** `tg_game:tron_log:byTxHash`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| txHash | string | 是 | 交易哈希 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "group_id": 1,
    "tx_hash": "abc123...",
    "from_address": "TXxx...xxxxx",
    "to_address": "TYyy...yyyyy",
    "amount": 100.00,
    "block_height": 12345678,
    "block_timestamp": 1704700800,
    "status": "success",
    "is_valid": true,
    "processed": true,
    "created_at": "2025-01-08 10:00:00"
  }
}
```

### 10.5 根据钱包地址查询交易

**接口地址：** `GET /admin/tg_game/tron_log/by_address/{address}`

**权限代码：** `tg_game:tron_log:byAddress`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| address | string | 是 | 钱包地址 |

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| direction | string | 否 | 方向：from/to |
| limit | integer | 否 | 数量限制，默认50 |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "tx_hash": "abc123...",
      "from_address": "TXxx...xxxxx",
      "to_address": "TYyy...yyyyy",
      "amount": 100.00,
      "status": "success",
      "created_at": "2025-01-08 10:00:00"
    }
  ]
}
```

### 10.6 获取交易统计

**接口地址：** `GET /admin/tg_game/tron_log/statistics`

**权限代码：** `tg_game:tron_log:statistics`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| group_id | integer | 否 | 群组ID |
| date_start | string | 否 | 开始日期(YYYY-MM-DD) |
| date_end | string | 否 | 结束日期(YYYY-MM-DD) |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total_transactions": 500,
    "total_amount": 50000.00,
    "valid_transactions": 480,
    "invalid_transactions": 20,
    "processed_transactions": 475,
    "unprocessed_transactions": 25
  }
}
```

### 10.7 手动重新处理交易

**接口地址：** `POST /admin/tg_game/tron_log/{id}/reprocess`

**权限代码：** `tg_game:tron_log:reprocess`

**路径参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 交易日志ID |

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "success": true,
    "message": "重新处理成功",
    "node_created": true
  }
}
```

### 10.8 手动同步区块链交易

**接口地址：** `POST /admin/tg_game/tron_log/sync_transactions`

**权限代码：** `tg_game:tron_log:syncTransactions`

**请求参数：**
| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| group_id | integer | 否 | 群组ID |
| start_block | integer | 否 | 起始区块高度 |
| end_block | integer | 否 | 结束区块高度 |

**请求示例：**
```json
{
  "group_id": 1,
  "start_block": 12345000,
  "end_block": 12346000
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "success": true,
    "message": "同步成功",
    "synced_count": 150,
    "valid_count": 145,
    "invalid_count": 5
  }
}
```

---

## 附录

### A. 状态码定义

#### 群组状态
| 值 | 说明 |
|----|------|
| 1 | 正常 |
| 2 | 停用 |

#### 钱包变更状态
| 值 | 说明 |
|----|------|
| 1 | 正常 |
| 2 | 变更中 |

#### 蛇身节点状态
| 值 | 说明 |
|----|------|
| 1 | 活跃 |
| 2 | 已中奖 |
| 3 | 未中奖 |

#### 中奖记录状态
| 值 | 说明 |
|----|------|
| 1 | 待处理 |
| 2 | 转账中 |
| 3 | 已完成 |
| 4 | 失败 |
| 5 | 部分失败 |

#### 转账状态
| 值 | 说明 |
|----|------|
| 1 | 待处理 |
| 2 | 处理中 |
| 3 | 成功 |
| 4 | 失败 |

#### 派奖队列状态
| 值 | 说明 |
|----|------|
| 1 | 待处理 |
| 2 | 处理中 |
| 3 | 已完成 |
| 4 | 失败 |

#### 操作来源
| 值 | 说明 |
|----|------|
| 1 | 管理后台 |
| 2 | Telegram Bot |
| 3 | 系统自动 |

### B. 权限代码列表

所有接口的权限代码格式为：`tg_game:{模块}:{操作}`

示例：
- `tg_game:group:list` - 查看游戏群组列表
- `tg_game:config:create` - 创建群组配置
- `tg_game:prize:detail` - 查看中奖详情

### C. 错误码说明

| 错误码 | 说明 |
|--------|------|
| 1000 | 参数验证失败 |
| 2000 | 资源不存在 |
| 3000 | 权限不足 |
| 4000 | 业务逻辑错误 |
| 5000 | 系统错误 |

### D. 分页参数说明

所有列表接口均支持分页参数：
- `page`: 页码，默认1
- `limit`: 每页数量，默认10，最大100

分页响应格式：
```json
{
  "total": 总记录数,
  "per_page": 每页数量,
  "current_page": 当前页码,
  "last_page": 最后页码,
  "data": []
}
```

---

**文档版本：** v1.0
**最后更新：** 2025-01-08
**维护者：** 开发团队
