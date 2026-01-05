<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\TenantUserService;
use DI\Attribute\Inject;
use Illuminate\Support\Arr;
use support\Request;
use support\Response;

#[RestController("/tenant/permission")]
class PermissionController extends BasicController
{
    #[Inject]
    protected TenantUserService $userService;

    #[GetMapping('/menus')]
    public function menus(Request $request): Response
    {
        $jsonData = <<< JSON
[
{
  "id": 300,
  "parent_id": 0,
  "name": "TgGame",
  "meta": {
    "i18n": "tgGame.index",
    "icon": "mdi:gamepad-variant-outline",
    "type": "M",
    "affix": false,
    "cache": true,
    "title": "TG游戏管理",
    "hidden": false,
    "copyright": true,
    "activeName": "",
    "componentPath": "modules/",
    "componentSuffix": ".vue",
    "breadcrumbEnable": true
  },
  "path": "/tgGame",
  "component": "game/views/TgGameGroup/Index",
  "redirect": "",
  "status": 1,
  "sort": 0,
  "created_by": 1,
  "updated_by": 1,
  "created_at": "2026-01-05 10:00:00",
  "updated_at": "2026-01-05 10:00:00",
  "remark": "",
  "children": [
    {
      "id": 301,
      "parent_id": 300,
      "name": "TgGameGroup",
      "meta": {
        "i18n": "tgGameGroup.index",
        "icon": "mdi:account-group-outline",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "游戏群组",
        "hidden": false,
        "copyright": true,
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/tgGame/group",
      "component": "game/views/TgGameGroup/Index",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2026-01-05 10:00:00",
      "updated_at": "2026-01-05 10:00:00",
      "remark": "",
      "children": [
        {
          "id": 302,
          "parent_id": 301,
          "name": "tg_game:group:list",
          "meta": {
            "i18n": "tgGameMenu.tgGameGroup.list",
            "type": "B",
            "title": "群组列表"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 303,
          "parent_id": 301,
          "name": "tg_game:group:update",
          "meta": {
            "i18n": "tgGameMenu.tgGameGroup.update",
            "type": "B",
            "title": "修改群组配置"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 304,
          "parent_id": 301,
          "name": "tg_game:group:update_wallet",
          "meta": {
            "i18n": "tgGameMenu.tgGameGroup.updateWallet",
            "type": "B",
            "title": "更新收款钱包"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 305,
          "parent_id": 301,
          "name": "tg_game:group:cancel_wallet_change",
          "meta": {
            "i18n": "tgGameMenu.tgGameGroup.cancelWalletChange",
            "type": "B",
            "title": "取消钱包变更"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 306,
          "parent_id": 301,
          "name": "tg_game:group:update_hot_wallet",
          "meta": {
            "i18n": "tgGameMenu.tgGameGroup.updateHotWallet",
            "type": "B",
            "title": "更新热钱包"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 307,
          "parent_id": 301,
          "name": "tg_game:group:update_status",
          "meta": {
            "i18n": "tgGameMenu.tgGameGroup.updateStatus",
            "type": "B",
            "title": "更新状态"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 308,
          "parent_id": 301,
          "name": "tg_game:group:statistics",
          "meta": {
            "i18n": "tgGameMenu.tgGameGroup.statistics",
            "type": "B",
            "title": "查看统计"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        }
      ]
    },
    {
      "id": 310,
      "parent_id": 300,
      "name": "TgStatistics",
      "meta": {
        "i18n": "tgStatistics.index",
        "icon": "mdi:chart-line",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "数据统计",
        "hidden": false,
        "copyright": true,
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/tgGame/statistics",
      "component": "game/views/TgStatistics/Index",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2026-01-05 10:00:00",
      "updated_at": "2026-01-05 10:00:00",
      "remark": "",
      "children": [
        {
          "id": 311,
          "parent_id": 310,
          "name": "tg_game:statistics:overview",
          "meta": {
            "i18n": "tgGameMenu.tgStatistics.overview",
            "type": "B",
            "title": "数据概览"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 312,
          "parent_id": 310,
          "name": "tg_game:statistics:group_ranking",
          "meta": {
            "i18n": "tgGameMenu.tgStatistics.groupRanking",
            "type": "B",
            "title": "群组排行"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 313,
          "parent_id": 310,
          "name": "tg_game:statistics:daily",
          "meta": {
            "i18n": "tgGameMenu.tgStatistics.daily",
            "type": "B",
            "title": "每日统计"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        },
        {
          "id": 314,
          "parent_id": 310,
          "name": "tg_game:statistics:trend",
          "meta": {
            "i18n": "tgGameMenu.tgStatistics.trend",
            "type": "B",
            "title": "趋势分析"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 1,
          "updated_by": 1,
          "created_at": "2026-01-05 10:00:00",
          "updated_at": "2026-01-05 10:00:00",
          "remark": "",
          "children": []
        }
      ]
    }
  ]
},
  {
  "id": 61,
  "parent_id": 0,
  "name": "tenant",
  "meta": {
    "i18n": "tenant.index",
    "icon": "mdi:store-outline",
    "type": "M",
    "affix": false,
    "cache": true,
    "title": "租户管理",
    "hidden": false,
    "copyright": true,
    "componentPath": "modules/",
    "componentSuffix": ".vue",
    "breadcrumbEnable": true
  },
  "path": "/tenantManage",
  "component": "tenant/views/Tenant/index",
  "redirect": "",
  "status": 1,
  "sort": 0,
  "created_by": 0,
  "updated_by": 1,
  "created_at": "2025-06-19 18:21:40",
  "updated_at": "2025-07-23 05:43:05",
  "remark": "",
  "children": [
    {
      "id": 69,
      "parent_id": 61,
      "name": "tenant:tenantApp",
      "meta": {
        "i18n": "tenantApp.index",
        "icon": "ri:apps-line",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "租户应用",
        "hidden": false,
        "copyright": true,
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/tenantapp",
      "component": "tenant/views/TenantApp/Index",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 0,
      "updated_by": 1,
      "created_at": "2025-06-19 22:38:31",
      "updated_at": "2025-06-22 04:34:07",
      "remark": "",
      "children": [
        {
          "id": 70,
          "parent_id": 69,
          "name": "tenant:tenantApp:list",
          "meta": {
            "i18n": "tenantMenu.tenantApp.list",
            "type": "B",
            "title": "租户应用列表"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 22:38:31",
          "updated_at": "2025-06-22 04:34:07",
          "remark": "",
          "children": []
        },
        {
          "id": 71,
          "parent_id": 69,
          "name": "tenant:tenantApp:create",
          "meta": {
            "i18n": "tenantMenu.tenantApp.create",
            "type": "B",
            "title": "租户应用新增"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 22:38:31",
          "updated_at": "2025-06-22 04:34:07",
          "remark": "",
          "children": []
        },
        {
          "id": 72,
          "parent_id": 69,
          "name": "tenant:tenantApp:update",
          "meta": {
            "i18n": "tenantMenu.tenantApp.update",
            "type": "B",
            "title": "租户应用编辑"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 22:38:31",
          "updated_at": "2025-06-22 04:34:07",
          "remark": "",
          "children": []
        },
        {
          "id": 73,
          "parent_id": 69,
          "name": "tenant:tenantApp:delete",
          "meta": {
            "i18n": "tenantMenu.tenantApp.delete",
            "type": "B",
            "title": "租户应用删除"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 22:38:31",
          "updated_at": "2025-06-22 04:34:07",
          "remark": "",
          "children": []
        },
        {
          "id": 74,
          "parent_id": 69,
          "name": "tenant:tenantApp:recovery",
          "meta": {
            "i18n": "tenantMenu.tenantApp.recovery",
            "type": "B",
            "title": "租户应用回收站恢复"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 18:21:40",
          "updated_at": "2025-06-22 04:33:29",
          "remark": "",
          "children": []
        },
        {
          "id": 75,
          "parent_id": 69,
          "name": "tenant:tenantApp:realDelete",
          "meta": {
            "i18n": "tenantMenu.tenantApp.realDelete",
            "type": "B",
            "title": "清空回收站"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 18:21:40",
          "updated_at": "2025-06-22 04:33:29",
          "remark": "",
          "children": []
        }
      ]
    },
    {
      "id": 76,
      "parent_id": 61,
      "name": "TenantUser",
      "meta": {
        "i18n": "tenantUser.index",
        "icon": "heroicons:user-group",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "租户成员",
        "hidden": false,
        "copyright": true,
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/tenant/TenantUser",
      "component": "tenant/views/TenantUser/Index",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 0,
      "updated_by": 1,
      "created_at": "2025-06-22 16:14:06",
      "updated_at": "2025-06-23 01:08:39",
      "remark": "",
      "children": [
        {
          "id": 77,
          "parent_id": 76,
          "name": "tenant:tenantUser:list",
          "meta": {
            "i18n": "tenantMenu.tenantUser.list",
            "type": "B",
            "title": "租户成员列表"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-22 16:14:06",
          "updated_at": "2025-06-23 01:08:39",
          "remark": "",
          "children": []
        },
        {
          "id": 78,
          "parent_id": 76,
          "name": "tenant:tenantUser:create",
          "meta": {
            "i18n": "tenantMenu.tenantUser.create",
            "type": "B",
            "title": "租户成员新增"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-22 16:14:06",
          "updated_at": "2025-06-23 01:08:39",
          "remark": "",
          "children": []
        },
        {
          "id": 79,
          "parent_id": 76,
          "name": "tenant:tenantUser:update",
          "meta": {
            "i18n": "tenantMenu.tenantUser.update",
            "type": "B",
            "title": "租户成员编辑"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-22 16:14:06",
          "updated_at": "2025-06-23 01:08:39",
          "remark": "",
          "children": []
        },
        {
          "id": 80,
          "parent_id": 76,
          "name": "tenant:tenantUser:delete",
          "meta": {
            "i18n": "tenantMenu.tenantUser.delete",
            "type": "B",
            "title": "租户成员删除"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-22 16:14:06",
          "updated_at": "2025-06-23 01:08:40",
          "remark": "",
          "children": []
        },
        {
          "id": 81,
          "parent_id": 76,
          "name": "tenant:tenantUser:recovery",
          "meta": {
            "i18n": "tenantMenu.tenantUser.recovery",
            "type": "B",
            "title": "租户用户回收站恢复"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 18:21:40",
          "updated_at": "2025-06-22 04:33:29",
          "remark": "",
          "children": []
        },
        {
          "id": 82,
          "parent_id": 76,
          "name": "tenant:tenantUser:realDelete",
          "meta": {
            "i18n": "tenantMenu.tenantUser.realDelete",
            "type": "B",
            "title": "清空回收站"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 18:21:40",
          "updated_at": "2025-06-22 04:33:29",
          "remark": "",
          "children": []
        },
        {
          "id": 83,
          "parent_id": 76,
          "name": "tenant:tenantUser:password",
          "meta": {
            "i18n": "tenantMenu.tenantUser.password",
            "type": "B",
            "title": "密码重置"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-06-19 18:21:40",
          "updated_at": "2025-06-22 04:33:29",
          "remark": "",
          "children": []
        }
      ]
    }
  ]
}]
JSON;

        return $this->success(json_decode($jsonData, true));
    }

    #[GetMapping('/roles')]
    public function roles(Request $request): Response
    {
        //     {
        //      "id": 1,
        //      "name": "\u8d85\u7ea7\u7ba1\u7406\u5458",
        //      "code": "SuperAdmin",
        //      "status": 1,
        //      "sort": 0,
        //      "created_by": 0,
        //      "updated_by": 0,
        //      "created_at": "2025-06-05 05:30:40",
        //      "updated_at": "2025-06-05 05:30:40",
        //      "remark": ""
        //    }
        return $this->success([
            [
                'id'         => 1,
                'name'       => '超级管理员',
                'code'       => 'SuperAdmin',
                'status'     => 1,
                'sort'       => 0,
                'created_by' => 0,
                'updated_by' => 0,
                'created_at' => '2025-06-05 05:30:40',
                'updated_at' => '2025-06-05 05:30:40',
                'remark'     => '',
            ]
        ]);
    }

    #[PostMapping('/update')]
    public function update(Request $request): Response
    {
        $validator = validate($request->post(), [
            'new_password'              => 'sometimes|confirmed|string|min:8',
            'new_password_confirmation' => 'sometimes|string|min:8',
            'old_password'              => ['sometimes', 'string'],
            'avatar'                    => 'sometimes|string|max:255',
            'backend_setting'           => 'sometimes|array',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $user = $request->user;
        if (Arr::exists($validatedData, 'new_password')) {
            if (!$user->verifyPassword(Arr::get($validatedData, 'old_password'))) {
                throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, trans('old_password_error', [], 'user'));
            }
            $validatedData['password'] = $validatedData['new_password'];
        }
        $this->userService->updateById($user->id, $validatedData);
        return $this->success();
    }
}
