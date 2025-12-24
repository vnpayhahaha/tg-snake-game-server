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
  "id": 168,
  "parent_id": 0,
  "name": "CollectionOrder",
  "meta": {
    "i18n": "collection_order.index",
    "icon": "mdi:order-bool-ascending",
    "type": "M",
    "affix": false,
    "cache": true,
    "title": "新增顶级菜单",
    "hidden": false,
    "copyright": true,
    "activeName": "",
    "componentPath": "modules/",
    "componentSuffix": ".vue",
    "breadcrumbEnable": true
  },
  "path": "/transaction/CollectionOrder",
  "component": "transaction/views/CollectionOrder/Index",
  "redirect": "",
  "status": 1,
  "sort": 0,
  "created_by": 1,
  "updated_by": 1,
  "created_at": "2025-07-14 03:59:27",
  "updated_at": "2025-07-24 07:54:39",
  "remark": "",
  "children": [
    {
      "id": 170,
      "parent_id": 168,
      "name": "CollectionOrderAll",
      "meta": {
        "i18n": "collection_order.all",
        "icon": "ri:menu-search-line",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "收款订单",
        "hidden": false,
        "copyright": true,
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/transaction/CollectionOrder",
      "component": "transaction/views/CollectionOrder/Index",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 0,
      "updated_by": 1,
      "created_at": "2025-07-12 12:56:47",
      "updated_at": "2025-07-14 04:06:14",
      "remark": "",
      "children": [
        {
          "id": 171,
          "parent_id": 170,
          "name": "transaction:collection_order:list",
          "meta": {
            "i18n": "menu.collection_order.list",
            "type": "B",
            "title": "收款订单列表"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 12:56:47",
          "updated_at": "2025-07-14 04:06:14",
          "remark": "",
          "children": []
        },
        {
          "id": 172,
          "parent_id": 170,
          "name": "transaction:collection_order:create",
          "meta": {
            "i18n": "menu.collection_order.create",
            "type": "B",
            "title": "收款订单新增"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 12:56:47",
          "updated_at": "2025-07-14 04:06:15",
          "remark": "",
          "children": []
        },
        {
          "id": 173,
          "parent_id": 170,
          "name": "transaction:collection_order:update",
          "meta": {
            "i18n": "menu.collection_order.update",
            "type": "B",
            "title": "收款订单编辑"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 12:56:47",
          "updated_at": "2025-07-14 04:06:15",
          "remark": "",
          "children": []
        },
        {
          "id": 174,
          "parent_id": 170,
          "name": "transaction:collection_order:delete",
          "meta": {
            "i18n": "menu.collection_order.delete",
            "type": "B",
            "title": "收款订单删除"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 12:56:47",
          "updated_at": "2025-07-14 04:06:15",
          "remark": "",
          "children": []
        }
      ]
    },
    {
      "id": 193,
      "parent_id": 168,
      "name": "CollectionOrderProcessing",
      "meta": {
        "i18n": "enums.CollectionOrder.status.processing",
        "icon": "ep:alarm-clock",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/CollectionOrder/processing",
      "component": "transaction/views/CollectionOrder/processing",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-04 11:25:26",
      "updated_at": "2025-08-04 11:33:42",
      "remark": "",
      "children": []
    },
    {
      "id": 194,
      "parent_id": 168,
      "name": "CollectionOrderSuccess",
      "meta": {
        "i18n": "enums.CollectionOrder.status.success",
        "icon": "ep:circle-check",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/CollectionOrder/success",
      "component": "transaction/views/CollectionOrder/success",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-04 11:32:34",
      "updated_at": "2025-08-04 11:39:56",
      "remark": "",
      "children": []
    },
    {
      "id": 195,
      "parent_id": 168,
      "name": "CollectionOrderSuspend",
      "meta": {
        "i18n": "enums.CollectionOrder.status.suspend",
        "icon": "ep:link",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/CollectionOrder/suspend",
      "component": "transaction/views/CollectionOrder/suspend",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-04 11:36:14",
      "updated_at": "2025-08-04 11:41:49",
      "remark": "",
      "children": []
    },
    {
      "id": 196,
      "parent_id": 168,
      "name": "CollectionOrderFail",
      "meta": {
        "i18n": "enums.CollectionOrder.status.fail",
        "icon": "mdi:cancel",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/CollectionOrder/fail",
      "component": "transaction/views/CollectionOrder/fail",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-04 11:39:26",
      "updated_at": "2025-08-04 11:43:36",
      "remark": "",
      "children": []
    },
    {
      "id": 197,
      "parent_id": 168,
      "name": "CollectionOrderCancel",
      "meta": {
        "i18n": "enums.CollectionOrder.status.cancel",
        "icon": "ep:circle-close",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/CollectionOrder/cancel",
      "component": "transaction/views/CollectionOrder/cancel",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-04 11:43:16",
      "updated_at": "2025-08-04 11:43:47",
      "remark": "",
      "children": []
    },
    {
      "id": 198,
      "parent_id": 168,
      "name": "CollectionOrderInvalid",
      "meta": {
        "i18n": "enums.CollectionOrder.status.invalid",
        "icon": "mdi:timer-cancel-outline",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/CollectionOrder/invalid",
      "component": "transaction/views/CollectionOrder/invalid",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-04 11:44:45",
      "updated_at": "2025-08-04 11:44:58",
      "remark": "",
      "children": []
    },
    {
      "id": 199,
      "parent_id": 168,
      "name": "CollectionOrderRefund",
      "meta": {
        "i18n": "enums.CollectionOrder.status.refund",
        "icon": "mdi:credit-card-refund-outline",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/CollectionOrder/refund",
      "component": "transaction/views/CollectionOrder/refund",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-04 11:46:45",
      "updated_at": "2025-08-04 11:46:56",
      "remark": "",
      "children": []
    }
  ]
},
{
  "id": 175,
  "parent_id": 0,
  "name": "DisbursementOrder",
  "meta": {
    "i18n": "disbursement_order.index",
    "icon": "mdi:order-bool-descending",
    "type": "M",
    "affix": false,
    "cache": true,
    "title": "新增顶级菜单",
    "hidden": false,
    "copyright": true,
    "activeName": "",
    "componentPath": "modules/",
    "componentSuffix": ".vue",
    "breadcrumbEnable": true
  },
  "path": "/transaction/DisbursementOrder",
  "component": "transaction/views/DisbursementOrder/Index",
  "redirect": "",
  "status": 1,
  "sort": 0,
  "created_by": 1,
  "updated_by": 1,
  "created_at": "2025-07-14 08:46:55",
  "updated_at": "2025-07-24 07:54:50",
  "remark": "",
  "children": [
    {
      "id": 178,
      "parent_id": 175,
      "name": "DisbursementOrderAll",
      "meta": {
        "i18n": "disbursement_order.all",
        "icon": "ri:menu-search-line",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "付款订单",
        "hidden": false,
        "copyright": true,
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/transaction/DisbursementOrder",
      "component": "transaction/views/DisbursementOrder/Index",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 0,
      "updated_by": 1,
      "created_at": "2025-07-12 22:34:46",
      "updated_at": "2025-07-14 08:53:29",
      "remark": "",
      "children": [
        {
          "id": 179,
          "parent_id": 178,
          "name": "transaction:disbursement_order:list",
          "meta": {
            "i18n": "menu.disbursement_order.list",
            "type": "B",
            "title": "付款订单列表"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 22:34:46",
          "updated_at": "2025-07-14 08:53:29",
          "remark": "",
          "children": []
        },
        {
          "id": 180,
          "parent_id": 178,
          "name": "transaction:disbursement_order:create",
          "meta": {
            "i18n": "menu.disbursement_order.create",
            "type": "B",
            "title": "付款订单新增"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 22:34:46",
          "updated_at": "2025-07-14 08:53:29",
          "remark": "",
          "children": []
        },
        {
          "id": 181,
          "parent_id": 178,
          "name": "transaction:disbursement_order:update",
          "meta": {
            "i18n": "menu.disbursement_order.update",
            "type": "B",
            "title": "付款订单编辑"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 22:34:46",
          "updated_at": "2025-07-14 08:53:29",
          "remark": "",
          "children": []
        },
        {
          "id": 182,
          "parent_id": 178,
          "name": "transaction:disbursement_order:delete",
          "meta": {
            "i18n": "menu.disbursement_order.delete",
            "type": "B",
            "title": "付款订单删除"
          },
          "path": "",
          "component": "",
          "redirect": "",
          "status": 1,
          "sort": 0,
          "created_by": 0,
          "updated_by": 0,
          "created_at": "2025-07-12 22:34:46",
          "updated_at": "2025-07-14 08:53:30",
          "remark": "",
          "children": []
        }
      ]
    },
    {
      "id": 200,
      "parent_id": 175,
      "name": "DisbursementOrderCreate",
      "meta": {
        "i18n": "enums.disbursement_order.status.create",
        "icon": "ri:apps-2-add-line",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/create",
      "component": "transaction/views/DisbursementOrder/create",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:09:52",
      "updated_at": "2025-08-10 13:16:36",
      "remark": "",
      "children": []
    },
    {
      "id": 201,
      "parent_id": 175,
      "name": "DisbursementOrderWaitPay",
      "meta": {
        "i18n": "enums.disbursement_order.status.wait_pay",
        "icon": "ri:paypal-line",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/waitPay",
      "component": "transaction/views/DisbursementOrder/waitPay",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:22:11",
      "updated_at": "2025-08-10 13:22:41",
      "remark": "",
      "children": []
    },
    {
      "id": 202,
      "parent_id": 175,
      "name": "DisbursementOrderWaitFill",
      "meta": {
        "i18n": "enums.disbursement_order.status.wait_fill",
        "icon": "mdi:chat-processing-outline",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/waitFill",
      "component": "transaction/views/DisbursementOrder/waitFill",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:31:20",
      "updated_at": "2025-08-10 13:47:55",
      "remark": "",
      "children": []
    },
    {
      "id": 203,
      "parent_id": 175,
      "name": "DisbursementOrderSuccess",
      "meta": {
        "i18n": "enums.disbursement_order.status.success",
        "icon": "ri:wechat-pay-line",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/success",
      "component": "transaction/views/DisbursementOrder/success",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:49:27",
      "updated_at": "2025-08-10 13:49:45",
      "remark": "",
      "children": []
    },
    {
      "id": 204,
      "parent_id": 175,
      "name": "DisbursementOrderSuspend",
      "meta": {
        "i18n": "enums.disbursement_order.status.suspend",
        "icon": "ant-design:warning-outlined",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/suspend",
      "component": "transaction/views/DisbursementOrder/suspend",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:50:31",
      "updated_at": "2025-08-10 14:00:52",
      "remark": "",
      "children": []
    },
    {
      "id": 205,
      "parent_id": 175,
      "name": "DisbursementOrderFail",
      "meta": {
        "i18n": "enums.disbursement_order.status.fail",
        "icon": "mdi:cancel",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/fail",
      "component": "transaction/views/DisbursementOrder/fail",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:50:31",
      "updated_at": "2025-08-10 13:51:45",
      "remark": "",
      "children": []
    },
    {
      "id": 206,
      "parent_id": 175,
      "name": "DisbursementOrderCancel",
      "meta": {
        "i18n": "enums.disbursement_order.status.cancel",
        "icon": "ep:circle-close",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/cancel",
      "component": "transaction/views/DisbursementOrder/cancel",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:52:34",
      "updated_at": "2025-08-10 13:53:05",
      "remark": "",
      "children": []
    },
    {
      "id": 207,
      "parent_id": 175,
      "name": "DisbursementOrderInvalid",
      "meta": {
        "i18n": "enums.disbursement_order.status.invalid",
        "icon": "mdi:timer-cancel-outline",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/invalid",
      "component": "transaction/views/DisbursementOrder/invalid",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 13:53:45",
      "updated_at": "2025-08-10 13:54:01",
      "remark": "",
      "children": []
    },
    {
      "id": 208,
      "parent_id": 175,
      "name": "DisbursementOrderRefund",
      "meta": {
        "i18n": "enums.disbursement_order.status.refund",
        "icon": "mdi:credit-card-refund-outline",
        "type": "M",
        "affix": false,
        "cache": true,
        "title": "新菜单",
        "hidden": false,
        "copyright": true,
        "activeName": "",
        "componentPath": "modules/",
        "componentSuffix": ".vue",
        "breadcrumbEnable": true
      },
      "path": "/DisbursementOrder/refund",
      "component": "transaction/views/DisbursementOrder/refund",
      "redirect": "",
      "status": 1,
      "sort": 0,
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-08-10 14:03:48",
      "updated_at": "2025-08-10 14:04:14",
      "remark": "",
      "children": []
    }
  ] 
},
  {
    "id": 130,
    "parent_id": 0,
    "name": "TenantAccountBillRecord",
    "meta": {
      "i18n": "tenantAccountRecord.tenantAccountBillRecord",
      "icon": "ant-design:snippets-outlined",
      "type": "M",
      "affix": false,
      "cache": true,
      "title": "新增顶级菜单",
      "hidden": false,
      "copyright": true,
      "activeName": "",
      "componentPath": "modules/",
      "componentSuffix": ".vue",
      "breadcrumbEnable": true
    },
    "path": "/TenantAccountRecord/all",
    "component": "tenant/views/TenantAccountRecord/Index",
    "redirect": "",
    "status": 1,
    "sort": 0,
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-07-10 04:59:07",
    "updated_at": "2025-07-25 05:51:55",
    "remark": "",
    "children": [
      {
        "id": 131,
        "parent_id": 130,
        "name": "TenantAccountRecord_all",
        "meta": {
          "i18n": "tenantAccountRecord.index",
          "icon": "ant-design:file-search-outlined",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "账单记录",
          "hidden": false,
          "copyright": true,
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/TenantAccountRecord/all",
        "component": "tenant/views/TenantAccountRecord/Index",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 0,
        "updated_by": 1,
        "created_at": "2025-07-03 19:47:13",
        "updated_at": "2025-07-14 03:55:09",
        "remark": "",
        "children": [
          {
            "id": 132,
            "parent_id": 131,
            "name": "tenant:tenant_account_record:list",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-14 03:55:09",
            "remark": "",
            "children": []
          },
          {
            "id": 133,
            "parent_id": 131,
            "name": "tenant:tenant_account_record:create",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.create",
              "type": "B",
              "title": "账单记录新增"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-14 03:55:09",
            "remark": "",
            "children": []
          },
          {
            "id": 134,
            "parent_id": 131,
            "name": "tenant:tenant_account_record:update",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.update",
              "type": "B",
              "title": "账单记录编辑"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-14 03:55:10",
            "remark": "",
            "children": []
          },
          {
            "id": 135,
            "parent_id": 131,
            "name": "tenant:tenant_account_record:delete",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.delete",
              "type": "B",
              "title": "账单记录删除"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-14 03:55:10",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 136,
        "parent_id": 130,
        "name": "TenantAccountRecord_transaction",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.transaction",
          "icon": "ant-design:ordered-list-outlined",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "交易账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/TenantAccountRecord/transaction",
        "component": "tenant/views/TenantAccountRecord/transaction",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 14:22:15",
        "updated_at": "2025-07-10 14:32:51",
        "remark": "",
        "children": [
          {
            "id": 137,
            "parent_id": 136,
            "name": "tenant:tenant_account_record:list_transaction",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "交易账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 138,
        "parent_id": 130,
        "name": "tenantAccountRecord_refund",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.refund",
          "icon": "mdi:credit-card-refund-outline",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "退款账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/TenantAccountRecord/refund",
        "component": "tenant/views/TenantAccountRecord/refund",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 14:34:13",
        "updated_at": "2025-07-10 14:35:08",
        "remark": "",
        "children": [
          {
            "id": 139,
            "parent_id": 138,
            "name": "tenant:tenant_account_record:list_refund",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "退款账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 140,
        "parent_id": 130,
        "name": "tenantAccountRecord_manual_add",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.manual_add",
          "icon": "ri:file-add-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "上分账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/TenantAccountRecord/manual_add",
        "component": "tenant/views/TenantAccountRecord/manualAdd",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 14:45:55",
        "updated_at": "2025-07-10 14:54:09",
        "remark": "",
        "children": [
          {
            "id": 141,
            "parent_id": 140,
            "name": "tenant:tenant_account_record:list_manual_add",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "上分账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 14:54:09",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 142,
        "parent_id": 130,
        "name": "tenantAccountRecord_manual_sub",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.manual_sub",
          "icon": "ri:file-reduce-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "下分账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/TenantAccountRecord/manual_sub",
        "component": "tenant/views/TenantAccountRecord/manualSub",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 14:47:43",
        "updated_at": "2025-07-10 14:54:24",
        "remark": "",
        "children": [
          {
            "id": 143,
            "parent_id": 142,
            "name": "tenant:tenant_account_record:list_manual_sub",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "下分账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 144,
        "parent_id": 130,
        "name": "tenantAccountRecord_freeze",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.freeze",
          "icon": "ri:file-forbid-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "冻结账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/tenantAccountRecord/freeze",
        "component": "tenant/views/TenantAccountRecord/freeze",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 15:03:40",
        "updated_at": "2025-07-10 15:03:59",
        "remark": "",
        "children": [
          {
            "id": 145,
            "parent_id": 144,
            "name": "tenant:tenant_account_record:list_freeze",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "冻结账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 146,
        "parent_id": 130,
        "name": "tenantAccountRecord_unfreeze",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.unfreeze",
          "icon": "ri:file-shield-2-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "解冻账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/tenantAccountRecord/unfreeze",
        "component": "tenant/views/TenantAccountRecord/unfreeze",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 15:05:26",
        "updated_at": "2025-07-10 15:05:46",
        "remark": "",
        "children": [
          {
            "id": 147,
            "parent_id": 146,
            "name": "tenant:tenant_account_record:list_unfreeze",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "解冻账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 148,
        "parent_id": 130,
        "name": "tenantAccountRecord_transfer_in",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.transfer_in",
          "icon": "ri:login-box-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "转入",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/tenantAccountRecord/transfer_in",
        "component": "tenant/views/TenantAccountRecord/transferIn",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 15:09:05",
        "updated_at": "2025-07-10 15:09:25",
        "remark": "",
        "children": [
          {
            "id": 149,
            "parent_id": 148,
            "name": "tenant:tenant_account_record:list_transfer_in",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "转入账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 150,
        "parent_id": 130,
        "name": "tenantAccountRecord_transfer_out",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.transfer_out",
          "icon": "ri:logout-box-r-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "转出账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/tenantAccountRecord/transfer_out",
        "component": "tenant/views/TenantAccountRecord/transferOut",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 15:10:24",
        "updated_at": "2025-07-10 15:11:27",
        "remark": "",
        "children": [
          {
            "id": 151,
            "parent_id": 150,
            "name": "tenant:tenant_account_record:list_transfer_out",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "转出账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 152,
        "parent_id": 130,
        "name": "tenantAccountRecord_reverse",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.reverse",
          "icon": "ri:file-check-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "冲正账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/tenantAccountRecord/reverse",
        "component": "tenant/views/TenantAccountRecord/reverse",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 15:14:12",
        "updated_at": "2025-07-10 15:14:23",
        "remark": "",
        "children": [
          {
            "id": 153,
            "parent_id": 152,
            "name": "tenant:tenant_account_record:list_reverse",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "冲正账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
            "remark": "",
            "children": []
          }
        ]
      },
      {
        "id": 154,
        "parent_id": 130,
        "name": "tenantAccountRecord_reversal",
        "meta": {
          "i18n": "enums.tenantAccountRecord.change_type.reversal",
          "icon": "ri:file-warning-line",
          "type": "M",
          "affix": false,
          "cache": true,
          "title": "反转账单",
          "hidden": false,
          "copyright": true,
          "activeName": "",
          "componentPath": "modules/",
          "componentSuffix": ".vue",
          "breadcrumbEnable": true
        },
        "path": "/tenantAccountRecord/reversal",
        "component": "tenant/views/TenantAccountRecord/reversal",
        "redirect": "",
        "status": 1,
        "sort": 0,
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-07-10 15:16:56",
        "updated_at": "2025-07-10 15:17:20",
        "remark": "",
        "children": [
          {
            "id": 155,
            "parent_id": 154,
            "name": "tenant:tenant_account_record:list_reversal",
            "meta": {
              "i18n": "tenantMenu.tenant_account_record.list",
              "type": "B",
              "title": "反转账单记录列表"
            },
            "path": "",
            "component": "",
            "redirect": "",
            "status": 1,
            "sort": 0,
            "created_by": 0,
            "updated_by": 0,
            "created_at": "2025-07-03 19:47:13",
            "updated_at": "2025-07-10 05:13:26",
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
