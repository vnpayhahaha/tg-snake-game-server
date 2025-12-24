/*
 Navicat MySQL Data Transfer

 Source Server         : 165.154.229.207
 Source Server Type    : MySQL
 Source Server Version : 50744
 Source Host           : 165.154.229.207:3306
 Source Schema         : newpay

 Target Server Type    : MySQL
 Target Server Version : 50744
 File Encoding         : 65001

 Date: 14/06/2025 01:45:15
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for attachment
-- ----------------------------
DROP TABLE IF EXISTS `attachment`;
CREATE TABLE `attachment`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '文件信息ID',
  `storage_mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储模式:local=本地,oss=阿里云,qiniu=七牛云,cos=腾讯云',
  `origin_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '原文件名',
  `object_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '新文件名',
  `hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文件hash',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'MIME类型',
  `storage_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '存储路径',
  `base_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '基础存储路径',
  `suffix` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文件扩展名',
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件访问地址',
  `size_byte` bigint(20) NULL DEFAULT NULL COMMENT '文件大小，单位字节',
  `size_info` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文件大小，有单位',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '附加属性备注',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建用户',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '更新用户',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '上传文件信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of attachment
-- ----------------------------
INSERT INTO `attachment` VALUES (1, 'local', 'dsfsdfsd', 'dddd', NULL, NULL, NULL, '', NULL, 'ddd', NULL, NULL, NULL, NULL, NULL, NULL, 0);
INSERT INTO `attachment` VALUES (2, 'local', 'bg1.png', '5fd6828a9b2ebd70c162ec2707791dd2.png', '5fd6828a9b2ebd70c162ec2707791dd2', 'image/png', '/upload/5fd6828a9b2ebd70c162ec2707791dd2.png', '/upload/5fd6828a9b2ebd70c162ec2707791dd2.png', 'png', 'http://127.0.0.1:9501/upload/5fd6828a9b2ebd70c162ec2707791dd2.png', 4721405, '4.5 MB', NULL, '2025-06-11 19:23:28', '2025-06-11 19:23:28', NULL, 0);
INSERT INTO `attachment` VALUES (3, 'local', '微信图片_20231208210744.jpg', 'c9f0129e4429f63e3b9e0a5da07a48ad.jpg', 'c9f0129e4429f63e3b9e0a5da07a48ad', 'image/jpeg', '/data/project/byapay/newpay/public/upload/c9f0129e4429f63e3b9e0a5da07a48ad.jpg', '/upload/c9f0129e4429f63e3b9e0a5da07a48ad.jpg', 'jpg', 'http://127.0.0.1:8899/upload/c9f0129e4429f63e3b9e0a5da07a48ad.jpg', 660521, '645.04 KB', NULL, '2025-06-11 19:30:25', '2025-06-11 19:30:25', NULL, 0);
INSERT INTO `attachment` VALUES (4, 'local', 'bg.png', '57f0e5984eb485d460e4ee7a3c381096.png', '57f0e5984eb485d460e4ee7a3c381096', 'image/png', '/data/project/byapay/newpay/public/upload/57f0e5984eb485d460e4ee7a3c381096.png', '/upload/57f0e5984eb485d460e4ee7a3c381096.png', 'png', 'http://127.0.0.1:9501/upload/57f0e5984eb485d460e4ee7a3c381096.png', 4195473, '4 MB', NULL, '2025-06-11 19:33:52', '2025-06-11 19:33:52', NULL, 0);

-- ----------------------------
-- Table structure for data_permission_policy
-- ----------------------------
DROP TABLE IF EXISTS `data_permission_policy`;
CREATE TABLE `data_permission_policy`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '用户ID（与角色二选一）',
  `position_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '岗位ID（与用户二选一）',
  `policy_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '策略类型（DEPT_SELF, DEPT_TREE, ALL, SELF, CUSTOM_DEPT, CUSTOM_FUNC）',
  `is_default` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否默认策略（默认值：true）',
  `value` json NULL COMMENT '策略值',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '数据权限策略' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of data_permission_policy
-- ----------------------------

-- ----------------------------
-- Table structure for department
-- ----------------------------
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '部门名称',
  `parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '父级部门ID',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '部门表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of department
-- ----------------------------
INSERT INTO `department` VALUES (1, '运营部门', 0, '2025-06-11 18:23:32', '2025-06-11 18:23:32', NULL);
INSERT INTO `department` VALUES (2, '运营部门1', 0, '2025-06-11 18:25:17', '2025-06-11 18:25:17', NULL);

-- ----------------------------
-- Table structure for dept_leader
-- ----------------------------
DROP TABLE IF EXISTS `dept_leader`;
CREATE TABLE `dept_leader`  (
  `dept_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '部门领导表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dept_leader
-- ----------------------------

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `parent_id` bigint(20) UNSIGNED NOT NULL COMMENT '父ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '菜单名称',
  `meta` json NULL COMMENT '附加属性',
  `path` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `component` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
  `redirect` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '重定向地址',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态:1=正常,2=停用',
  `sort` smallint(6) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` bigint(20) NOT NULL DEFAULT 0 COMMENT '更新者',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `remark` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `menu_name_unique`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 47 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '菜单信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES (1, 0, 'permission', '{\"i18n\": \"baseMenu.permission.index\", \"icon\": \"ri:git-repository-private-line\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"权限管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/permission', '', '', 1, 0, 0, 0, '2025-06-05 05:30:30', '2025-06-05 05:30:30', '');
INSERT INTO `menu` VALUES (2, 1, 'permission:user', '{\"i18n\": \"baseMenu.permission.user\", \"icon\": \"material-symbols:manage-accounts-outline\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"用户管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/permission/user', 'base/views/permission/user/index', '', 1, 0, 0, 0, '2025-06-05 05:30:31', '2025-06-05 05:30:31', '');
INSERT INTO `menu` VALUES (3, 2, 'permission:user:index', '{\"i18n\": \"baseMenu.permission.userList\", \"type\": \"B\", \"title\": \"用户列表\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:31', '2025-06-05 05:30:31', '');
INSERT INTO `menu` VALUES (4, 2, 'permission:user:save', '{\"i18n\": \"baseMenu.permission.userSave\", \"type\": \"B\", \"title\": \"用户保存\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:31', '2025-06-05 05:30:31', '');
INSERT INTO `menu` VALUES (5, 2, 'permission:user:update', '{\"i18n\": \"baseMenu.permission.userUpdate\", \"type\": \"B\", \"title\": \"用户更新\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:31', '2025-06-05 05:30:31', '');
INSERT INTO `menu` VALUES (6, 2, 'permission:user:delete', '{\"i18n\": \"baseMenu.permission.userDelete\", \"type\": \"B\", \"title\": \"用户删除\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:31', '2025-06-05 05:30:31', '');
INSERT INTO `menu` VALUES (7, 2, 'permission:user:password', '{\"i18n\": \"baseMenu.permission.userPassword\", \"type\": \"B\", \"title\": \"用户初始化密码\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:31', '2025-06-05 05:30:31', '');
INSERT INTO `menu` VALUES (8, 2, 'permission:user:getRole', '{\"i18n\": \"baseMenu.permission.getUserRole\", \"type\": \"B\", \"title\": \"获取用户角色\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:32', '2025-06-05 05:30:37', '');
INSERT INTO `menu` VALUES (9, 2, 'permission:user:setRole', '{\"i18n\": \"baseMenu.permission.setUserRole\", \"type\": \"B\", \"title\": \"用户角色赋予\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:32', '2025-06-05 05:30:37', '');
INSERT INTO `menu` VALUES (10, 1, 'permission:menu', '{\"i18n\": \"baseMenu.permission.menu\", \"icon\": \"ph:list-bold\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"菜单管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/permission/menu', 'base/views/permission/menu/index', '', 1, 0, 0, 0, '2025-06-05 05:30:32', '2025-06-05 05:30:32', '');
INSERT INTO `menu` VALUES (11, 10, 'permission:menu:index', '{\"i18n\": \"baseMenu.permission.menuList\", \"type\": \"B\", \"title\": \"菜单列表\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:32', '2025-06-05 05:30:32', '');
INSERT INTO `menu` VALUES (12, 10, 'permission:menu:create', '{\"i18n\": \"baseMenu.permission.menuSave\", \"type\": \"B\", \"title\": \"菜单保存\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:32', '2025-06-05 05:30:32', '');
INSERT INTO `menu` VALUES (13, 10, 'permission:menu:save', '{\"i18n\": \"baseMenu.permission.menuUpdate\", \"type\": \"B\", \"title\": \"菜单更新\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:33', '2025-06-05 05:30:33', '');
INSERT INTO `menu` VALUES (14, 10, 'permission:menu:delete', '{\"i18n\": \"baseMenu.permission.menuDelete\", \"type\": \"B\", \"title\": \"菜单删除\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:33', '2025-06-05 05:30:33', '');
INSERT INTO `menu` VALUES (15, 1, 'permission:role', '{\"i18n\": \"baseMenu.permission.role\", \"icon\": \"material-symbols:supervisor-account-outline-rounded\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"角色管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/permission/role', 'base/views/permission/role/index', '', 1, 0, 0, 0, '2025-06-05 05:30:33', '2025-06-05 05:30:33', '');
INSERT INTO `menu` VALUES (16, 15, 'permission:role:index', '{\"i18n\": \"baseMenu.permission.roleList\", \"type\": \"B\", \"title\": \"角色列表\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:33', '2025-06-05 05:30:33', '');
INSERT INTO `menu` VALUES (17, 15, 'permission:role:save', '{\"i18n\": \"baseMenu.permission.roleSave\", \"type\": \"B\", \"title\": \"角色保存\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:33', '2025-06-05 05:30:33', '');
INSERT INTO `menu` VALUES (18, 15, 'permission:role:update', '{\"i18n\": \"baseMenu.permission.roleUpdate\", \"type\": \"B\", \"title\": \"角色更新\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:33', '2025-06-05 05:30:33', '');
INSERT INTO `menu` VALUES (19, 15, 'permission:role:delete', '{\"i18n\": \"baseMenu.permission.roleDelete\", \"type\": \"B\", \"title\": \"角色删除\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:34', '2025-06-05 05:30:34', '');
INSERT INTO `menu` VALUES (20, 15, 'permission:role:getMenu', '{\"i18n\": \"baseMenu.permission.getRolePermission\", \"type\": \"B\", \"title\": \"获取角色权限\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:34', '2025-06-05 05:30:36', '');
INSERT INTO `menu` VALUES (21, 15, 'permission:role:setMenu', '{\"i18n\": \"baseMenu.permission.setRolePermission\", \"type\": \"B\", \"title\": \"赋予角色权限\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:34', '2025-06-05 05:30:37', '');
INSERT INTO `menu` VALUES (22, 0, 'log', '{\"i18n\": \"baseMenu.log.index\", \"icon\": \"ph:instagram-logo\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"日志管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/log', '', '', 1, 0, 0, 0, '2025-06-05 05:30:34', '2025-06-05 05:30:34', '');
INSERT INTO `menu` VALUES (23, 22, 'log:userLogin', '{\"i18n\": \"baseMenu.log.userLoginLog\", \"icon\": \"ph:user-list\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"用户登录日志管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/log/userLoginLog', 'base/views/log/userLogin', '', 1, 0, 0, 0, '2025-06-05 05:30:34', '2025-06-05 05:30:34', '');
INSERT INTO `menu` VALUES (24, 23, 'log:userLogin:list', '{\"i18n\": \"baseMenu.log.userLoginLogList\", \"type\": \"B\", \"title\": \"用户登录日志列表\"}', '/log/userLoginLog', '', '', 1, 0, 0, 0, '2025-06-05 05:30:35', '2025-06-05 05:30:35', '');
INSERT INTO `menu` VALUES (25, 23, 'log:userLogin:delete', '{\"i18n\": \"baseMenu.log.userLoginLogDelete\", \"type\": \"B\", \"title\": \"删除用户登录日志\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:35', '2025-06-05 05:30:35', '');
INSERT INTO `menu` VALUES (26, 22, 'log:userOperation', '{\"i18n\": \"baseMenu.log.operationLog\", \"icon\": \"ph:list-magnifying-glass\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"操作日志管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/log/operationLog', 'base/views/log/userOperation', '', 1, 0, 0, 0, '2025-06-05 05:30:35', '2025-06-05 05:30:35', '');
INSERT INTO `menu` VALUES (27, 26, 'log:userOperation:list', '{\"i18n\": \"baseMenu.log.userOperationLog\", \"type\": \"B\", \"title\": \"用户操作日志列表\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:35', '2025-06-05 05:30:35', '');
INSERT INTO `menu` VALUES (28, 26, 'log:userOperation:delete', '{\"i18n\": \"baseMenu.log.userOperationLogDelete\", \"type\": \"B\", \"title\": \"删除用户操作日志\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:35', '2025-06-05 05:30:35', '');
INSERT INTO `menu` VALUES (29, 0, 'dataCenter', '{\"i18n\": \"baseMenu.dataCenter.index\", \"icon\": \"ri:database-line\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"数据中心\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/dataCenter', '', '', 1, 0, 0, 0, '2025-06-05 05:30:35', '2025-06-05 05:30:35', '');
INSERT INTO `menu` VALUES (30, 29, 'dataCenter:attachment', '{\"i18n\": \"baseMenu.dataCenter.attachment\", \"icon\": \"ri:attachment-line\", \"type\": \"M\", \"affix\": false, \"cache\": true, \"title\": \"附件管理\", \"hidden\": false, \"copyright\": true, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": true}', '/dataCenter/attachment', 'base/views/dataCenter/attachment/index', '', 1, 0, 0, 0, '2025-06-05 05:30:36', '2025-06-05 05:30:36', '');
INSERT INTO `menu` VALUES (31, 30, 'dataCenter:attachment:list', '{\"i18n\": \"baseMenu.dataCenter.attachmentList\", \"type\": \"B\", \"title\": \"附件列表\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:36', '2025-06-05 05:30:36', '');
INSERT INTO `menu` VALUES (32, 30, 'dataCenter:attachment:upload', '{\"i18n\": \"baseMenu.dataCenter.attachmentUpload\", \"type\": \"B\", \"title\": \"上传附件\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:36', '2025-06-05 05:30:36', '');
INSERT INTO `menu` VALUES (33, 30, 'dataCenter:attachment:delete', '{\"i18n\": \"baseMenu.dataCenter.attachmentDelete\", \"type\": \"B\", \"title\": \"删除附件\"}', '', '', '', 1, 0, 0, 0, '2025-06-05 05:30:36', '2025-06-05 05:30:36', '');
INSERT INTO `menu` VALUES (34, 1, 'permission:department', '{\"i18n\": \"baseMenu.permission.department\", \"icon\": \"mingcute:department-line\", \"type\": \"M\", \"affix\": 0, \"cache\": 1, \"title\": \"部门管理\", \"hidden\": 0, \"copyright\": 1, \"componentPath\": \"modules/\", \"componentSuffix\": \".vue\", \"breadcrumbEnable\": 1}', '/permission/departments', 'base/views/permission/department/index', '', 1, 0, 0, 0, '2025-06-05 05:30:37', '2025-06-05 05:30:37', '');
INSERT INTO `menu` VALUES (35, 34, 'permission:department:index', '{\"i18n\": \"baseMenu.permission.departmentList\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"部门列表\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:37', '2025-06-05 05:30:37', '');
INSERT INTO `menu` VALUES (36, 34, 'permission:department:save', '{\"i18n\": \"baseMenu.permission.departmentCreate\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"部门新增\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:38', '2025-06-05 05:30:38', '');
INSERT INTO `menu` VALUES (37, 34, 'permission:department:update', '{\"i18n\": \"baseMenu.permission.departmentSave\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"部门编辑\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:38', '2025-06-05 05:30:38', '');
INSERT INTO `menu` VALUES (38, 34, 'permission:department:delete', '{\"i18n\": \"baseMenu.permission.departmentDelete\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"部门删除\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:38', '2025-06-05 05:30:38', '');
INSERT INTO `menu` VALUES (39, 34, 'permission:position:index', '{\"i18n\": \"baseMenu.permission.positionList\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"岗位列表\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:38', '2025-06-05 05:30:38', '');
INSERT INTO `menu` VALUES (40, 34, 'permission:position:save', '{\"i18n\": \"baseMenu.permission.positionCreate\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"岗位新增\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:38', '2025-06-05 05:30:38', '');
INSERT INTO `menu` VALUES (41, 34, 'permission:position:update', '{\"i18n\": \"baseMenu.permission.positionSave\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"岗位编辑\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:38', '2025-06-05 05:30:38', '');
INSERT INTO `menu` VALUES (42, 34, 'permission:position:delete', '{\"i18n\": \"baseMenu.permission.positionDelete\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"岗位删除\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:39', '2025-06-05 05:30:39', '');
INSERT INTO `menu` VALUES (43, 34, 'permission:position:data_permission', '{\"i18n\": \"baseMenu.permission.positionDataScope\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"设置岗位数据权限\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:39', '2025-06-05 05:30:39', '');
INSERT INTO `menu` VALUES (44, 34, 'permission:leader:index', '{\"i18n\": \"baseMenu.permission.leaderList\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"部门领导列表\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:39', '2025-06-05 05:30:39', '');
INSERT INTO `menu` VALUES (45, 34, 'permission:leader:save', '{\"i18n\": \"baseMenu.permission.leaderCreate\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"新增部门领导\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:39', '2025-06-05 05:30:39', '');
INSERT INTO `menu` VALUES (46, 34, 'permission:leader:delete', '{\"i18n\": \"baseMenu.permission.leaderDelete\", \"type\": \"B\", \"affix\": 0, \"cache\": 1, \"title\": \"部门领导移除\", \"hidden\": 1}', '/permission/departments', '', '', 1, 0, 0, 0, '2025-06-05 05:30:39', '2025-06-05 05:30:39', '');

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2020_07_22_213202_create_rules_table', 1);
INSERT INTO `migrations` VALUES (2, '2021_04_12_160526_create_user_table', 1);
INSERT INTO `migrations` VALUES (3, '2021_04_18_215320_create_menu_table', 1);
INSERT INTO `migrations` VALUES (4, '2021_04_18_215515_create_role_table', 1);
INSERT INTO `migrations` VALUES (5, '2021_06_24_111216_create_attachment_table', 1);
INSERT INTO `migrations` VALUES (6, '2024_09_22_205304_create_user_login_log', 1);
INSERT INTO `migrations` VALUES (7, '2024_09_22_205631_create_user_operation_log', 1);
INSERT INTO `migrations` VALUES (8, '2024_10_31_193302_create_user_belongs_role', 1);
INSERT INTO `migrations` VALUES (9, '2024_10_31_204004_create_role_belongs_menu', 1);
INSERT INTO `migrations` VALUES (10, '2025_02_24_195620_create_department_tables', 2);

-- ----------------------------
-- Table structure for position
-- ----------------------------
DROP TABLE IF EXISTS `position`;
CREATE TABLE `position`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '岗位名称',
  `dept_id` bigint(20) NOT NULL COMMENT '部门ID',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '岗位表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of position
-- ----------------------------

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色代码',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态:1=正常,2=停用',
  `sort` smallint(6) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` bigint(20) NOT NULL DEFAULT 0 COMMENT '更新者',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `role_code_unique`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '角色信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES (1, '超级管理员', 'SuperAdmin', 1, 0, 0, 0, '2025-06-05 05:30:40', '2025-06-05 05:30:40', '');
INSERT INTO `role` VALUES (2, 'test', 'test', 1, 0, 0, 0, NULL, NULL, '');
INSERT INTO `role` VALUES (3, 'ddd', 'wwe', 1, 2, 1, 1, NULL, NULL, '');
INSERT INTO `role` VALUES (5, 'haha', 'hahas', 1, 2, 1, 0, NULL, NULL, '');
INSERT INTO `role` VALUES (6, 'ddd', 'ww', 1, 4, 1, 0, NULL, NULL, '');

-- ----------------------------
-- Table structure for role_belongs_menu
-- ----------------------------
DROP TABLE IF EXISTS `role_belongs_menu`;
CREATE TABLE `role_belongs_menu`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) NOT NULL COMMENT '角色id',
  `menu_id` bigint(20) NOT NULL COMMENT '菜单id',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_belongs_menu
-- ----------------------------

-- ----------------------------
-- Table structure for rules
-- ----------------------------
DROP TABLE IF EXISTS `rules`;
CREATE TABLE `rules`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ptype` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `v0` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `v1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `v2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `v3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `v4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `v5` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of rules
-- ----------------------------

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config`  (
  `id` bigint(20) NOT NULL COMMENT '配置ID',
  `group_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分组编码',
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '唯一编码',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '配置名称',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '配置内容',
  `is_sys` tinyint(1) NULL DEFAULT 0 COMMENT '是否系统',
  `enabled` tinyint(1) NULL DEFAULT 1 COMMENT '是否启用',
  `created_at` bigint(20) NULL DEFAULT NULL COMMENT '创建时间',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建用户',
  `updated_at` bigint(20) NULL DEFAULT NULL COMMENT '更新时间',
  `updated_by` bigint(20) NULL DEFAULT 0 COMMENT '更新用户',
  `deleted_at` bigint(20) NULL DEFAULT NULL COMMENT '是否删除',
  `remark` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_config_code`(`code`) USING BTREE,
  INDEX `idx_config_group_code`(`group_code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of system_config
-- ----------------------------
INSERT INTO `system_config` VALUES (378369306427400192, 'local', 'root', '', 'public', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369307895406592, 'local', 'dirname', '', 'upload', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369309388578816, 'local', 'domain', '', 'http://127.0.0.1:9501', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369310898528256, 'oss', 'accessKeyId', '', '1', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369312400089088, 'oss', 'accessKeySecret', '', '2', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369313935204352, 'oss', 'bucket', '', '3', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369315394822144, 'oss', 'dirname', '', '4', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369316913160192, 'oss', 'domain', '', '5', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369318406332416, 'oss', 'endpoint', '', '6', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369319907893248, 'oss', 'remark', '', '7', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369321367511040, 'cos', 'secretId', '', '11', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369322810351616, 'cos', 'secretKey', '', '22', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369324261580800, 'cos', 'bucket', '', '33', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369325704421376, 'cos', 'dirname', '', '44', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369327164039168, 'cos', 'domain', '', '55', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369328707543040, 'cos', 'region', '', '66', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369330200715264, 'cos', 'remark', '', '77', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369331643555840, 'qiniu', 'accessKey', '', '99', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369333103173632, 'qiniu', 'secretKey', '', '88', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369334554402816, 'qiniu', 'bucket', '', '7', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369336089518080, 'qiniu', 'dirname', '', '78', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369337557524480, 'qiniu', 'domain', '', '8', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369339101028352, 'qiniu', 'region', '', '', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369340552257536, 'qiniu', 'remark', '', '897', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369341995098112, 's3', 'key', '', '12', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369343437938688, 's3', 'secret', '', '12', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369344889167872, 's3', 'bucket', '', '12', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369346340397056, 's3', 'dirname', '', '12', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369347833569280, 's3', 'domain', '', '12', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369349301575680, 's3', 'endpoint', '', '12', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369350761193472, 's3', 'region', '', '12', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369352204034048, 's3', 'acl', '', '6', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369353672040448, 's3', 'remark', '', '4', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369355148435456, 'email_setting', 'SMTPSecure', '', 'ssl', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369356599664640, 'email_setting', 'Host', '', 'smtp.qq.com', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369358076059648, 'email_setting', 'Port', '', '465', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369359544066048, 'email_setting', 'Username', '', 'kzhzjdyw888@qq.com', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369361070792704, 'email_setting', 'Password', '', '', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369362538799104, 'email_setting', 'From', '', '', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369364040359936, 'email_setting', 'FromName', '', 'kzhzjdyw888@qq.com', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369365508366336, 'basic_upload_setting', 'mode', '上传模式', 'local', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369366959595520, 'basic_upload_setting', 'single_limit', '上传大小', '1024', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369368410824704, 'basic_upload_setting', 'total_limit', '文件限制', '1024', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369369853665280, 'basic_upload_setting', 'nums', '数量限制', '10', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369371304894464, 'basic_upload_setting', 'exclude', '不允许文件类型', 'php,ext,exe', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369372772900864, 'site_setting', 'site_open', '站点开启', '1', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369374224130048, 'site_setting', 'site_url', '网站地址', '127.0.0.1:8899', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369375683747840, 'site_setting', 'site_name', '站点名称', 'MaDong Admin', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369377126588416, 'site_setting', 'site_logo', '站点Logo', '', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369378577817600, 'site_setting', 'site_network_security', '网备案号', '', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369380129710080, 'site_setting', 'site_description', '网站描述', '快速开发框架', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369381580939264, 'site_setting', 'site_record_no', '网站ICP', '', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369383023779840, 'site_setting', 'site_icp_url', 'ICP URL', 'https=>>//beian.miit.gov.cn/', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369384475009024, 'site_setting', 'site_network_security_url', '网安备案链接', '', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369385984958464, 'sms_setting', 'enable', '是否开启', '1', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369387444576256, 'sms_setting', 'access_key_id', 'access_key_id', '234813346262818816', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369388912582656, 'sms_setting', 'access_key_secret', 'access_key_secret', '238164553517768704', 0, 1, NULL, NULL, NULL, 0, NULL, '');
INSERT INTO `system_config` VALUES (378369390355423232, 'sms_setting', 'sign_name', 'sign_name', '【码动开源】，你的验证码是{code}，有效期5分钟。', 0, 1, NULL, NULL, NULL, 0, NULL, '');

-- ----------------------------
-- Table structure for system_user
-- ----------------------------
DROP TABLE IF EXISTS `system_user`;
CREATE TABLE `system_user`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID，主键',
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `user_type` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '100' COMMENT '用户类型：(100系统用户)',
  `nickname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用户昵称',
  `phone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '手机',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用户邮箱',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用户头像',
  `signed` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '个人签名',
  `dashboard` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '后台首页类型',
  `status` smallint(6) NULL DEFAULT 1 COMMENT '状态 (1正常 2停用)',
  `login_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '最后登陆IP',
  `login_time` timestamp NULL DEFAULT NULL COMMENT '最后登陆时间',
  `backend_setting` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '后台设置数据',
  `created_by` bigint(20) NULL DEFAULT NULL COMMENT '创建者',
  `updated_by` bigint(20) NULL DEFAULT NULL COMMENT '更新者',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '备注',
  `is_google_verify` tinyint(1) NOT NULL DEFAULT 2 COMMENT '是否google验证(1yes 2no)',
  `google_secret_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Google验证密钥',
  `is_google_bind` tinyint(1) NOT NULL DEFAULT 2 COMMENT '是否已绑定Google验证(1yes 2no)',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `system_user_username_unique`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of system_user
-- ----------------------------

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID,主键',
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `user_type` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '100' COMMENT '用户类型:100=系统用户',
  `nickname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `phone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户头像',
  `signed` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '个人签名',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态:1=正常,2=停用',
  `login_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1' COMMENT '最后登陆IP',
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后登陆时间',
  `backend_setting` json NULL COMMENT '后台设置数据',
  `created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` bigint(20) NOT NULL DEFAULT 0 COMMENT '更新者',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_username_unique`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'admin', '$2y$10$5ueag8DJh3ScVEhSkUXZUuAyBL8iJHmTiqNsY9WKYoz/6ldA34ifu', '100', '创始人1', '16858888988', 'admin@adminmine.com', '', '广阔天地，大有所为', 1, '127.0.0.1', '2025-06-05 12:30:40', '{\"app\": {\"layout\": \"columns\", \"asideDark\": false, \"colorMode\": \"autoMode\", \"useLocale\": \"zh_CN\", \"whiteRoute\": [\"login\"], \"pageAnimate\": \"ma-slide-down\", \"primaryColor\": \"#2563EB\", \"watermarkText\": \"MineAdmin\", \"showBreadcrumb\": true, \"enableWatermark\": false, \"loadUserSetting\": true}, \"tabbar\": {\"mode\": \"rectangle\", \"enable\": true}, \"subAside\": {\"showIcon\": true, \"showTitle\": true, \"fixedAsideState\": false, \"showCollapseButton\": true}, \"copyright\": {\"dates\": \"2025\", \"enable\": false, \"company\": \"MineAdmin Team\", \"website\": \"https://www.mineadmin.com\", \"putOnRecord\": \"豫ICP备00000000号-1\"}, \"mainAside\": {\"showIcon\": true, \"showTitle\": true, \"enableOpenFirstRoute\": false}, \"welcomePage\": {\"icon\": \"icon-park-outline:jewelry\", \"name\": \"welcome\", \"path\": \"/welcome\", \"title\": \"欢迎页\"}}', 0, 0, '2025-06-05 05:30:40', '2025-06-05 05:48:12', '');
INSERT INTO `user` VALUES (4, 'test2', '$2y$10$mT//LwEu4mrwVuFVRa2anekjwsAnK3mcDMeBt1QshAJ4/lWqMYy5u', '200', '张三', '', '', '', '', 1, '127.0.0.1', '2025-06-11 01:51:48', NULL, 1, 0, NULL, '2025-06-11 03:16:31', '');
INSERT INTO `user` VALUES (5, 'test3', '$2y$10$gqrlgxdJ1jtzn92X3JaNPOK/CPMti5yeqo6XxitBfGSrQOA3JeAQi', '200', '张三', '', '', '', '', 1, '127.0.0.1', '2025-06-11 01:54:27', NULL, 1, 0, '2025-06-11 02:54:28', '2025-06-11 02:54:28', '');
INSERT INTO `user` VALUES (6, 'test6', '$2y$10$scdrkdlthmXiMYzbO/bff.Bh/MTpBhhu6Derb5dVK/3VllavPk3tK', '200', '张三', '', '', '', '', 1, '127.0.0.1', '2025-06-11 04:11:27', NULL, 1, 0, '2025-06-11 05:11:27', '2025-06-11 05:11:27', '');
INSERT INTO `user` VALUES (7, 'test7', '$2y$10$JoEi4Z5Joxh7vsOeYtDRjuZDJv5F/5Y4fPqvCp.ZUlVsl9ItJv1u2', '200', '张三', '', '', '', '', 1, '127.0.0.1', '2025-06-11 04:15:19', NULL, 1, 0, '2025-06-11 05:15:19', '2025-06-11 05:15:19', '');
INSERT INTO `user` VALUES (8, 'test0', '$2y$10$QijZN6iI/LnjNg.DgKXzgu/qh/HJn2IO.hTEIKqq7gxKjLy1RCsq2', '200', '张三', '', '', '', '', 1, '127.0.0.1', '2025-06-11 04:17:33', NULL, 1, 0, '2025-06-11 05:17:33', '2025-06-11 05:17:33', '');
INSERT INTO `user` VALUES (10, 'test23', '$2y$10$TCicjhrgEcjP.l0S2oMLUexYS1GzlggxZXbzuCr3c7CGkjBls/JNe', '200', '张三', '', '', '', '', 1, '127.0.0.1', '2025-06-11 04:41:30', NULL, 1, 0, '2025-06-11 05:41:30', '2025-06-11 05:41:30', '');
INSERT INTO `user` VALUES (13, 'test31', '$2y$10$wiv.BzXRJ6909PsUFaHGnuZufWzbbhwstmpPg2icpNrswXZcYCxrC', '200', '张三', '', '', '', '', 1, '127.0.0.1', '2025-06-13 03:43:12', NULL, 1, 0, '2025-06-13 04:43:12', '2025-06-13 04:43:12', '');

-- ----------------------------
-- Table structure for user_belongs_role
-- ----------------------------
DROP TABLE IF EXISTS `user_belongs_role`;
CREATE TABLE `user_belongs_role`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `role_id` bigint(20) NOT NULL COMMENT '角色id',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_belongs_role
-- ----------------------------
INSERT INTO `user_belongs_role` VALUES (1, 1, 1, NULL, NULL);
INSERT INTO `user_belongs_role` VALUES (2, 4, 2, NULL, NULL);
INSERT INTO `user_belongs_role` VALUES (3, 5, 2, NULL, NULL);

-- ----------------------------
-- Table structure for user_dept
-- ----------------------------
DROP TABLE IF EXISTS `user_dept`;
CREATE TABLE `user_dept`  (
  `user_id` bigint(20) NOT NULL,
  `dept_id` bigint(20) NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户-部门关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_dept
-- ----------------------------

-- ----------------------------
-- Table structure for user_login_log
-- ----------------------------
DROP TABLE IF EXISTS `user_login_log`;
CREATE TABLE `user_login_log`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '登录IP地址',
  `os` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '操作系统',
  `browser` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '浏览器',
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '登录状态 (1成功 2失败)',
  `message` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '提示消息',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_login_log_username_index`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '登录日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_login_log
-- ----------------------------
INSERT INTO `user_login_log` VALUES (1, 'admin', '172.17.0.1', 'Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, NULL, '2025-06-05 05:37:25', NULL);
INSERT INTO `user_login_log` VALUES (2, 'admin', '172.17.0.1', 'Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, NULL, '2025-06-05 05:49:57', NULL);
INSERT INTO `user_login_log` VALUES (3, 'admin', '172.17.0.1', 'Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 1, NULL, '2025-06-06 01:24:41', NULL);
INSERT INTO `user_login_log` VALUES (4, 'superAdmin', '172.17.0.1', 'Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 1, NULL, '2025-06-12 02:15:00', NULL);
INSERT INTO `user_login_log` VALUES (5, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-12 16:37:01', NULL);
INSERT INTO `user_login_log` VALUES (6, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-12 17:39:48', NULL);
INSERT INTO `user_login_log` VALUES (7, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-12 19:05:48', NULL);
INSERT INTO `user_login_log` VALUES (8, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-12 19:33:53', NULL);
INSERT INTO `user_login_log` VALUES (9, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-12 19:38:33', NULL);
INSERT INTO `user_login_log` VALUES (10, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-12 19:41:55', NULL);
INSERT INTO `user_login_log` VALUES (11, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-12 19:42:59', NULL);
INSERT INTO `user_login_log` VALUES (12, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-13 02:55:04', NULL);
INSERT INTO `user_login_log` VALUES (13, 'superAdmin', '172.17.0.1', 'Other', 'PostmanRuntime-ApipostRuntime/1.1.0', 1, NULL, '2025-06-13 03:54:20', NULL);
INSERT INTO `user_login_log` VALUES (14, 'superAdmin', '172.17.0.1', 'Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, NULL, '2025-06-14 01:41:58', NULL);
INSERT INTO `user_login_log` VALUES (15, 'superAdmin', '172.17.0.1', 'Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, NULL, '2025-06-14 01:42:57', NULL);
INSERT INTO `user_login_log` VALUES (16, 'superAdmin', '172.17.0.1', 'Other', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, NULL, '2025-06-14 01:43:35', NULL);

-- ----------------------------
-- Table structure for user_operation_log
-- ----------------------------
DROP TABLE IF EXISTS `user_operation_log`;
CREATE TABLE `user_operation_log`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `method` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求方式',
  `router` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求路由',
  `service_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '业务名称',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '请求IP地址',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '备注',
  `request_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '请求参数',
  `response_status` int(11) NULL DEFAULT NULL COMMENT '响应状态码',
  `is_success` tinyint(1) NULL DEFAULT 1 COMMENT '操作是否成功(1:成功,0:失败)',
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '响应数据',
  `operator_id` bigint(20) UNSIGNED NULL DEFAULT 0 COMMENT '操作者ID',
  `request_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'uuid',
  `request_duration` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '请求耗时(毫秒)',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_operation_log_username_index`(`username`) USING BTREE,
  INDEX `user_operation_log_created_at_index`(`created_at`) USING BTREE,
  INDEX `user_operation_log_service_name_index`(`service_name`) USING BTREE,
  INDEX `user_operation_log_operator_id_index`(`operator_id`) USING BTREE,
  INDEX `user_operation_log_operator_id_created_at_index`(`operator_id`, `created_at`) USING BTREE,
  INDEX `user_operation_log_created_at_service_name_index`(`created_at`, `service_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 46 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '操作日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_operation_log
-- ----------------------------
INSERT INTO `user_operation_log` VALUES (1, 'admin', 'GET', '/admin/department/list', '部门列表', '172.17.0.1', '2025-06-05 05:41:42', '2025-06-05 05:41:42', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (2, 'admin', 'GET', '/admin/department/list', '部门列表', '172.17.0.1', '2025-06-05 05:41:50', '2025-06-05 05:41:50', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (3, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:42:16', '2025-06-05 05:42:16', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (4, 'admin', 'POST', '/admin/attachment/upload', '上传附件', '172.17.0.1', '2025-06-05 05:43:17', '2025-06-05 05:43:17', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (5, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:43:19', '2025-06-05 05:43:19', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (6, 'admin', 'DELETE', '/admin/attachment/1', '@OA\\Generator::UNDEFINED🙈', '172.17.0.1', '2025-06-05 05:43:38', '2025-06-05 05:43:38', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (7, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:43:39', '2025-06-05 05:43:39', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (8, 'admin', 'POST', '/admin/attachment/upload', '上传附件', '172.17.0.1', '2025-06-05 05:43:59', '2025-06-05 05:43:59', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (9, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:44:01', '2025-06-05 05:44:01', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (10, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:44:23', '2025-06-05 05:44:23', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (11, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:44:27', '2025-06-05 05:44:27', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (12, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:44:29', '2025-06-05 05:44:29', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (13, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:50:01', '2025-06-05 05:50:01', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (14, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-05 05:54:51', '2025-06-05 05:54:51', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (15, 'admin', 'GET', '/admin/user/list', '用户列表', '172.17.0.1', '2025-06-05 05:55:56', '2025-06-05 05:55:56', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (16, 'admin', 'GET', '/admin/department/list', '部门列表', '172.17.0.1', '2025-06-05 05:55:56', '2025-06-05 05:55:56', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (17, 'admin', 'GET', '/admin/attachment/list', '附件列表', '172.17.0.1', '2025-06-06 01:25:41', '2025-06-06 01:25:41', '', NULL, NULL, 1, NULL, NULL, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (18, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:16:07', '2025-06-12 19:16:07', NULL, '[]', 200, 0, '{\"request_id\":\"a7a45d84-31db-4bb2-a4c8-585a00b2078e\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (19, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:16:10', '2025-06-12 19:16:10', NULL, '[]', 200, 0, '{\"request_id\":\"c3c24768-4683-411f-80be-cd4afeb69998\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (20, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:16:44', '2025-06-12 19:16:44', NULL, '[]', 200, 0, '{\"request_id\":\"d321197b-e1ff-46de-96ff-ffa58265b589\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (21, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:19:41', '2025-06-12 19:19:41', NULL, '[]', 200, 0, '{\"request_id\":\"5e8f8c90-bd37-45c9-9639-301ce9ef2be1\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (22, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:22:44', '2025-06-12 19:22:44', NULL, '[]', 200, 0, '{\"request_id\":\"37896a59-16d4-4420-a362-98ba3e610816\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (23, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:24:26', '2025-06-12 19:24:26', NULL, '[]', 200, 0, '{\"request_id\":\"3e06e931-27f5-400e-a967-899018f3991f\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (24, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:30:18', '2025-06-12 19:30:18', NULL, '[]', 200, 0, '{\"request_id\":\"866ff41a-f204-4b78-9604-063d4e18e38c\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (25, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:31:35', '2025-06-12 19:31:35', NULL, '[]', 200, 0, '{\"request_id\":\"7f184ac5-ea67-440a-8476-d3da4469bdb1\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (26, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:32:02', '2025-06-12 19:32:02', NULL, '[]', 200, 0, '{\"request_id\":\"5780baa5-4232-4d89-beac-544c28e7a83c\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (27, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:33:57', '2025-06-12 19:33:57', NULL, '[]', 200, 0, '{\"request_id\":\"cc573d2b-3783-41ee-8658-ec4fe82a9f42\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (28, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:37:22', '2025-06-12 19:37:22', NULL, '[]', 200, 0, '{\"request_id\":\"c302743c-fda8-467b-b3e3-eeb4a59fdf06\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (29, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:37:53', '2025-06-12 19:37:53', NULL, '[]', 200, 0, '{\"request_id\":\"7f49e84b-b3b4-4f0f-a24a-71d4f587903b\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (30, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:38:06', '2025-06-12 19:38:06', NULL, '[]', 401, 0, '{\"request_id\":\"14d9d16a-d613-44e7-80f1-fc81036fab06\",\"path\":\"\\/admin\\/role\\/list\",\"success\":false,\"code\":100401,\"message\":\"The token is in blacklist\"}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (31, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:38:21', '2025-06-12 19:38:21', NULL, '[]', 401, 0, '{\"request_id\":\"0b4843f6-dc18-44b4-b9cc-91ad6334029d\",\"path\":\"\\/admin\\/role\\/list\",\"success\":false,\"code\":100401,\"message\":\"The token is in blacklist\"}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (32, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:38:36', '2025-06-12 19:38:36', NULL, '[]', 200, 0, '{\"request_id\":\"85e947a2-06cd-4831-a6ca-11b574b1326e\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (33, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:41:18', '2025-06-12 19:41:18', NULL, '[]', 200, 0, '{\"request_id\":\"45789feb-9a6d-4b2e-9d47-6ba6419d086a\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (34, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:41:34', '2025-06-12 19:41:34', NULL, '[]', 401, 0, '{\"request_id\":\"d37f9ea7-23a4-40fe-bb70-934186d50b64\",\"path\":\"\\/admin\\/role\\/list\",\"success\":false,\"code\":100401,\"message\":\"The token is in blacklist\"}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (35, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:43:06', '2025-06-12 19:43:06', NULL, '[]', 200, 0, '{\"request_id\":\"10a42897-cc1d-4023-8cb3-af30f21da4a9\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (36, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:43:19', '2025-06-12 19:43:19', NULL, '[]', 401, 0, '{\"request_id\":\"a5af44b4-1a00-4c93-bda4-c7af36fc2f5d\",\"path\":\"\\/admin\\/role\\/list\",\"success\":false,\"code\":100401,\"message\":\"The token is in blacklist\"}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (37, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-12 19:43:55', '2025-06-12 19:43:55', NULL, '[]', 401, 0, '{\"request_id\":\"63589d69-59e2-4a52-97db-47789ca5dc86\",\"path\":\"\\/admin\\/role\\/list\",\"success\":false,\"code\":100401,\"message\":\"The token is in blacklist\"}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (38, 'no_login_user', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 02:54:40', '2025-06-13 02:54:40', NULL, '[]', 401, 2, '{\"request_id\":\"4cf1457e-7ee0-40d7-a7d1-8739e73248b5\",\"path\":\"\\/admin\\/role\\/list\",\"success\":false,\"code\":100401,\"message\":\"The token is expired.\"}', 0, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (39, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 02:55:09', '2025-06-13 02:55:09', NULL, '[]', 200, 2, '{\"request_id\":\"864818f4-d095-48ed-84e8-cc496a443113\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, NULL, NULL);
INSERT INTO `user_operation_log` VALUES (40, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 03:17:43', '2025-06-13 03:17:43', NULL, '[]', 200, 2, '{\"request_id\":\"a76854e6-f706-4794-98d3-977105f7112d\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, 'a76854e6-f706-4794-98d3-977105f7112d', 2647);
INSERT INTO `user_operation_log` VALUES (41, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 03:17:55', '2025-06-13 03:17:55', NULL, '[]', 200, 2, '{\"request_id\":\"03490d1c-dad2-40c8-8585-8e9031552c55\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, '03490d1c-dad2-40c8-8585-8e9031552c55', 406);
INSERT INTO `user_operation_log` VALUES (42, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 03:20:50', '2025-06-13 03:20:50', NULL, '[]', 200, 2, '{\"request_id\":\"109b47ff-5723-42c1-a973-317359993649\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, '109b47ff-5723-42c1-a973-317359993649', 2869);
INSERT INTO `user_operation_log` VALUES (43, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 03:21:25', '2025-06-13 03:21:25', NULL, '[]', 200, 2, '{\"request_id\":\"dc04b365-f054-4145-8861-9460ccddb4ef\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, 'dc04b365-f054-4145-8861-9460ccddb4ef', 2759);
INSERT INTO `user_operation_log` VALUES (44, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 03:25:08', '2025-06-13 03:25:08', NULL, '[]', 200, 1, '{\"request_id\":\"d6050918-8105-4131-9907-b80a6308cfc1\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, 'd6050918-8105-4131-9907-b80a6308cfc1', 2842);
INSERT INTO `user_operation_log` VALUES (45, 'superAdmin', 'GET', '/admin/role/list', '获取角色', '172.17.0.1', '2025-06-13 03:25:24', '2025-06-13 03:25:24', NULL, '[]', 200, 1, '{\"request_id\":\"c5f54061-bd8e-4957-bb1c-65e2a3e1b137\",\"path\":\"\\/admin\\/role\\/list\",\"success\":true,\"code\":200,\"message\":\"\\u6210\\u529f\",\"data\":{\"list\":[{\"id\":1,\"name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"code\":\"SuperAdmin\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":\"2025-06-04T21:30:40.000000Z\",\"updated_at\":\"2025-06-04T21:30:40.000000Z\",\"remark\":\"\"},{\"id\":2,\"name\":\"test\",\"code\":\"test\",\"status\":1,\"sort\":0,\"created_by\":0,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":3,\"name\":\"ddd\",\"code\":\"wwe\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":1,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":5,\"name\":\"haha\",\"code\":\"hahas\",\"status\":1,\"sort\":2,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"},{\"id\":6,\"name\":\"ddd\",\"code\":\"ww\",\"status\":1,\"sort\":4,\"created_by\":1,\"updated_by\":0,\"created_at\":null,\"updated_at\":null,\"remark\":\"\"}],\"total\":5}}', 1, 'c5f54061-bd8e-4957-bb1c-65e2a3e1b137', 457);

-- ----------------------------
-- Table structure for user_position
-- ----------------------------
DROP TABLE IF EXISTS `user_position`;
CREATE TABLE `user_position`  (
  `user_id` bigint(20) NOT NULL,
  `position_id` bigint(20) NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户-岗位关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_position
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
