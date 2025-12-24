/*
 Navicat Premium Dump SQL

 Source Server         : 165.154.229.207
 Source Server Type    : MySQL
 Source Server Version : 50744 (5.7.44-log)
 Source Host           : 165.154.229.207:3306
 Source Schema         : newpay

 Target Server Type    : MySQL
 Target Server Version : 50744 (5.7.44-log)
 File Encoding         : 65001

 Date: 20/09/2025 14:50:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for attachment
-- ----------------------------
DROP TABLE IF EXISTS `attachment`;
CREATE TABLE `attachment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '文件信息ID',
  `storage_mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储模式:local=本地,oss=阿里云,qiniu=七牛云,cos=腾讯云',
  `origin_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '原文件名',
  `object_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '新文件名',
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件hash',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'MIME类型',
  `storage_path` longtext COLLATE utf8mb4_unicode_ci COMMENT '存储路径',
  `base_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '基础存储路径',
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件扩展名',
  `url` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件访问地址',
  `size_byte` bigint(20) DEFAULT NULL COMMENT '文件大小，单位字节',
  `size_info` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件大小，有单位',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '附加属性备注',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_by` bigint(20) DEFAULT NULL COMMENT '创建用户',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '更新用户',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='上传文件信息表';

-- ----------------------------
-- Table structure for bank_account
-- ----------------------------
DROP TABLE IF EXISTS `bank_account`;
CREATE TABLE `bank_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '银行id',
  `branch_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支行名称',
  `account_holder` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '账户持有人',
  `account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '账号',
  `bank_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IFSC代码',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `float_amount_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代收小数浮动开关:0关闭 1启用',
  `daily_max_receipt` decimal(12,2) DEFAULT '0.00' COMMENT '单日最大收款限额',
  `daily_max_payment` decimal(12,2) DEFAULT '0.00' COMMENT '单日最大付款限额',
  `daily_max_receipt_count` int(10) unsigned DEFAULT '0' COMMENT '单日最大收款次数',
  `daily_max_payment_count` int(10) unsigned DEFAULT '0' COMMENT '单日最大付款次数',
  `max_receipt_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最大收款限额',
  `max_payment_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最大付款限额',
  `min_receipt_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最小收款限额',
  `min_payment_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最小付款限额',
  `security_level` tinyint(2) unsigned DEFAULT '1' COMMENT '安全等级(1-99)',
  `last_used_time` datetime DEFAULT NULL COMMENT '最后使用时间',
  `upi_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'UPI支付地址',
  `used_quota` decimal(12,2) DEFAULT '0.00' COMMENT '实际已用金额额度',
  `limit_quota` decimal(12,2) DEFAULT '0.00' COMMENT '限制使用金额额度',
  `today_receipt_count` int(10) unsigned DEFAULT '0' COMMENT '当日已收款次数',
  `today_payment_count` int(10) unsigned DEFAULT '0' COMMENT '当日已付款次数',
  `today_receipt_amount` decimal(15,2) DEFAULT '0.00' COMMENT '当日已收款金额',
  `today_payment_amount` decimal(15,2) DEFAULT '0.00' COMMENT '当日已付款金额',
  `stat_date` date DEFAULT NULL COMMENT '统计日期(YYYY-MM-DD)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1启用 2停用)',
  `support_collection` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持代收',
  `support_disbursement` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持代付',
  `down_bill_template_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]' COMMENT '付款账单模板项',
  `account_config` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]' COMMENT '账户配置',
  PRIMARY KEY (`id`),
  KEY `idx_channel_id` (`channel_id`),
  KEY `account_number` (`account_number`),
  KEY `idx_active_receivable` (`status`,`stat_date`,`today_receipt_amount`,`daily_max_receipt`),
  KEY `idx_active_payable` (`status`,`stat_date`,`today_payment_amount`,`daily_max_payment`),
  KEY `idx_usage_alert` (`stat_date`,`status`,`today_receipt_amount`,`daily_max_receipt`,`today_payment_amount`,`daily_max_payment`),
  KEY `idx_channel_operations` (`channel_id`,`status`,`last_used_time`),
  KEY `idx_limit_checks` (`status`,`stat_date`,`today_receipt_count`,`daily_max_receipt_count`,`today_payment_count`,`daily_max_payment_count`),
  KEY `idx_support_collection` (`support_collection`),
  KEY `idx_support_disbursement` (`support_disbursement`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='银行账户表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_axis
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_axis`;
CREATE TABLE `bank_disbursement_bill_axis` (
  `bill_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID/Primary Key',
  `sr_no` varchar(50) NOT NULL DEFAULT '' COMMENT '序号/Serial Number',
  `corporate_product` varchar(100) NOT NULL DEFAULT '' COMMENT '企业产品/Corporate Product',
  `payment_method` varchar(50) NOT NULL DEFAULT '' COMMENT '支付方式/Payment Method',
  `batch_no` varchar(50) NOT NULL DEFAULT '' COMMENT '批次号/Batch Number',
  `next_working_day_date` varchar(50) NOT NULL DEFAULT '' COMMENT '下一工作日日期/Next Working Day Date',
  `debit_account_no` varchar(50) NOT NULL DEFAULT '' COMMENT '借记账号/Debit Account Number',
  `corporate_account_description` varchar(200) NOT NULL DEFAULT '' COMMENT '企业账户描述/Corporate Account Description',
  `beneficiary_account_no` varchar(50) NOT NULL DEFAULT '' COMMENT '收款人账号/Beneficiary Account Number',
  `beneficiary_code` varchar(50) NOT NULL DEFAULT '' COMMENT '收款人代码/Beneficiary Code',
  `beneficiary_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人姓名/Beneficiary Name',
  `payee_name` varchar(100) NOT NULL DEFAULT '' COMMENT '付款人姓名/Payee Name',
  `currency` varchar(10) NOT NULL DEFAULT '' COMMENT '币种/Currency',
  `amount_payable` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '应付金额/Amount Payable',
  `transaction_status` varchar(50) NOT NULL DEFAULT '' COMMENT '交易状态/Transaction Status',
  `crn_no` varchar(50) NOT NULL DEFAULT '' COMMENT 'CRN编号/CRN Number',
  `paid_date` varchar(50) NOT NULL DEFAULT '' COMMENT '支付日期/Paid Date',
  `utr_reference_no` varchar(100) NOT NULL DEFAULT '' COMMENT 'UTR/RBI参考号/核心参考号/UTR/RBI Reference No./Core Ref No.',
  `funding_date` varchar(50) NOT NULL DEFAULT '' COMMENT '资金日期/Funding Date',
  `reason` varchar(200) NOT NULL DEFAULT '' COMMENT '原因/Reason',
  `remarks` varchar(500) NOT NULL DEFAULT '' COMMENT '备注/Remarks',
  `stage` varchar(50) NOT NULL DEFAULT '' COMMENT '阶段/Stage',
  `email_id` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱/Email ID',
  `clg_branch_name` varchar(100) NOT NULL DEFAULT '' COMMENT 'CLG分行名称/CLG Branch Name',
  `activation_date` varchar(50) NOT NULL DEFAULT '' COMMENT '激活日期/Activation Date',
  `payout_mode` varchar(50) NOT NULL DEFAULT '' COMMENT '支付模式/Payout Mode',
  `finacle_cheque_no` varchar(50) NOT NULL DEFAULT '' COMMENT 'Finacle支票号/Finacle Cheque No',
  `ifsc_code` varchar(50) NOT NULL DEFAULT '' COMMENT 'IFSC代码/MICR代码/IIN/IFSC Code/MICR Code/IIN',
  `bank_reference_no` varchar(100) NOT NULL DEFAULT '' COMMENT '银行参考号/Bank Reference No.',
  `account_number` varchar(50) NOT NULL DEFAULT '' COMMENT '账号/Account Number',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间/Create Time',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间/Update Time',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_batch_no` (`batch_no`) USING BTREE,
  KEY `idx_transaction_status` (`transaction_status`) USING BTREE,
  KEY `idx_paid_date` (`paid_date`) USING BTREE,
  KEY `idx_beneficiary_account` (`beneficiary_account_no`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE,
  KEY `idx_utr_reference` (`utr_reference_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='AXIS订单导入记录表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_axis_neft
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_axis_neft`;
CREATE TABLE `bank_disbursement_bill_axis_neft` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `receipient_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收款人名称',
  `account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '账户号码',
  `ifsc_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IFSC代码',
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '金额',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '失败原因',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_account_number` (`account_number`) USING BTREE,
  KEY `idx_ifsc_code` (`ifsc_code`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='axis NEFT支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_axis_neo
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_axis_neo`;
CREATE TABLE `bank_disbursement_bill_axis_neo` (
  `bill_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `srl_no` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '序列号',
  `tran_date` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '交易日期',
  `chq_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支票号',
  `particulars` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '摘要',
  `amount_inr` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '金额(INR)',
  `dr_cr` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '借/贷',
  `balance_inr` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '余额(INR)',
  `sol` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SOL',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_tran_date` (`tran_date`) USING BTREE,
  KEY `idx_chq_no` (`chq_no`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='axis NEO支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_bandhan
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_bandhan`;
CREATE TABLE `bank_disbursement_bill_bandhan` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `core_ref_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '核心参考号',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  `execution_time` datetime DEFAULT NULL COMMENT '执行时间',
  `error_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '错误代码',
  `payment_date` date DEFAULT NULL COMMENT '付款日期',
  `payment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '付款类型',
  `customer_ref_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户参考号',
  `source_account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '源账户号码',
  `source_narration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '源账户说明',
  `destination_account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '目标账户号码',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '币种',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `destination_narration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '目标账户说明',
  `destination_bank` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '目标银行',
  `destination_bank_routing_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '目标银行路由代码',
  `beneficiary_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '受益人名称',
  `beneficiary_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '受益人代码',
  `beneficiary_account_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '受益人账户类型',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '拒绝原因',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_core_ref_number` (`core_ref_number`) USING BTREE,
  KEY `idx_payment_date` (`payment_date`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE,
  KEY `idx_error_code` (`error_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='bandhan支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_icici
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_icici`;
CREATE TABLE `bank_disbursement_bill_icici` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `pymt_mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付模式(IMPS)',
  `file_sequence_num` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件序列号',
  `debit_acct_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '借记账户号码',
  `beneficiary_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '受益人名称',
  `beneficiary_account_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '受益人账户号',
  `bene_ifsc_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '受益人IFSC代码',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `pymt_date` date DEFAULT NULL COMMENT '支付日期',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '拒绝原因',
  `customer_ref_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户编号',
  `utr_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'UTR_NO',
  `order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE,
  KEY `idx_customer_ref_no` (`customer_ref_no`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_utr_no` (`utr_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='ICICI支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_icici_2
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_icici_2`;
CREATE TABLE `bank_disbursement_bill_icici_2` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `network_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '网络ID',
  `credit_account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '贷方账户号码',
  `debit_account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '借方账户号码',
  `ifsc_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IFSC代码',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
  `host_reference_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '主机参考号码',
  `transaction_remarks` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '交易备注',
  `transaction_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '交易状态',
  `transaction_status_remarks` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '交易状态备注',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '拒绝原因',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_network_id` (`network_id`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='icici支付账单表2';

-- ----------------------------
-- Table structure for bank_disbursement_bill_idfc
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_idfc`;
CREATE TABLE `bank_disbursement_bill_idfc` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `beneficiary_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收款人姓名/Beneficiary Name',
  `beneficiary_account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收款人账号/Beneficiary Account Number',
  `ifsc` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IFSC代码',
  `transaction_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '交易类型/Transaction Type',
  `debit_account_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '借记账号/Debit Account No',
  `transaction_date` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '交易日期/Transaction Date',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '金额/Amount',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '币种/Currency',
  `beneficiary_email_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收款人邮箱/Beneficiary Email ID',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注/Remarks',
  `utr_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'UTR编号/UTR Number',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态/Status',
  `errors` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '错误信息/Errors',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_utr_number` (`utr_number`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='IDFC支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_iob_other
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_iob_other`;
CREATE TABLE `bank_disbursement_bill_iob_other` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `s_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '序号',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `ifsc_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IFSC代码',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '编号',
  `amount` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '金额',
  `charges` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '费用',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `narration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '说明',
  `utr_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'UTR编号',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原因',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '拒绝原因',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_utr_no` (`utr_no`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='iob其他支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_iob_same
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_iob_same`;
CREATE TABLE `bank_disbursement_bill_iob_same` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `s_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '序号',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `ifsc_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IFSC代码',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '编号',
  `amount` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '金额',
  `charges` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '费用',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `narration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '说明',
  `utr_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'UTR编号',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原因',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '拒绝原因',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_utr_no` (`utr_no`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='iob同行支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_bill_yesmsme
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_bill_yesmsme`;
CREATE TABLE `bank_disbursement_bill_yesmsme` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `record` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '记录',
  `record_ref_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '记录参考号',
  `file_ref_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件参考号',
  `ebanking_ref_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '电子银行参考号',
  `contract_ref_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同参考号',
  `record_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '记录状态',
  `status_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态代码',
  `status_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态描述',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `upload_id` bigint(20) unsigned NOT NULL COMMENT '上传ID',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传源文件hash',
  PRIMARY KEY (`bill_id`) USING BTREE,
  KEY `idx_record_ref_no` (`record_ref_no`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `idx_upload_id` (`upload_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='yes msme支付账单表';

-- ----------------------------
-- Table structure for bank_disbursement_download
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_download`;
CREATE TABLE `bank_disbursement_download` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `attachment_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '资源ID',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '存储路径',
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件hash',
  `file_size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00' COMMENT '数据大小（M）',
  `record_count` int(11) NOT NULL DEFAULT '0' COMMENT '条数',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建者',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件扩展名',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_attachment_id` (`attachment_id`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE,
  KEY `idx_file_name` (`file_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='银行支付账单下载表';

-- ----------------------------
-- Table structure for bank_disbursement_upload
-- ----------------------------
DROP TABLE IF EXISTS `bank_disbursement_upload`;
CREATE TABLE `bank_disbursement_upload` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `attachment_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '资源ID',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '存储路径',
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件hash',
  `file_size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00' COMMENT '数据大小（M）',
  `record_count` int(11) NOT NULL DEFAULT '0' COMMENT '条数',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建者',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件扩展名',
  `channel_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '渠道ID',
  `upload_bill_template_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传模板IDi',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `parsing_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '解析状态：0失败，1成功',
  `success_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付成功数',
  `failure_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付失败数',
  `pending_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付中',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_attachment_id` (`attachment_id`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE,
  KEY `idx_file_name` (`file_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='银行支付账单上传表';

-- ----------------------------
-- Table structure for channel
-- ----------------------------
DROP TABLE IF EXISTS `channel`;
CREATE TABLE `channel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '渠道编码',
  `channel_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '渠道名称',
  `channel_icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '渠道图标',
  `channel_type` tinyint(2) NOT NULL COMMENT '渠道类型:1-银行 2-上游第三方支付',
  `country_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IN' COMMENT '国家代码(IN=印度)',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR' COMMENT '默认币种',
  `api_base_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API基础地址',
  `doc_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文档地址',
  `support_collection` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持代收',
  `support_disbursement` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持代付',
  `config` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]' COMMENT '渠道配置(JSON)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1-启用 0-停用',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_channel_code` (`channel_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付渠道表';

-- ----------------------------
-- Table structure for channel_account
-- ----------------------------
DROP TABLE IF EXISTS `channel_account`;
CREATE TABLE `channel_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` bigint(20) unsigned NOT NULL COMMENT '渠道ID',
  `merchant_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '渠道商户ID',
  `api_config` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]' COMMENT 'API配置 {\r\n   "api_key": "KEY-12345", \r\n      "api_secret": "SECRET-67890",\r\n      "token": "TOKEN-ABCDE",\r\n      "app_id": "APP-123"\r\n    "version": "v2.1.0",          -- 接口版本\r\n    "encryption": "RSA",           -- 加密方式\r\n    "sign_algo": "SHA256",         -- 签名算法\r\n    "request_format": "JSON"       -- 请求格式\r\n  }',
  `document_info` json DEFAULT NULL COMMENT '文档信息 {\r\n    "url": "https://doc.example.com/v2", \r\n    "access_code": "DOC-SECRET-123",\r\n  }',
  `api_version` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口版本',
  `callback_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '回调地址',
  `ip_whitelist` text COLLATE utf8mb4_unicode_ci COMMENT '回调请求IP白名单',
  `balance` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '渠道账户余额',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR' COMMENT '币种',
  `used_quota` decimal(12,2) DEFAULT '0.00' COMMENT '实际已用金额额度',
  `limit_quota` decimal(12,2) DEFAULT '0.00' COMMENT '限制使用金额额度',
  `today_receipt_count` int(10) unsigned DEFAULT '0' COMMENT '当日已收款次数',
  `today_payment_count` int(10) unsigned DEFAULT '0' COMMENT '当日已付款次数',
  `today_receipt_amount` decimal(15,2) DEFAULT '0.00' COMMENT '当日已收款金额',
  `today_payment_amount` decimal(15,2) DEFAULT '0.00' COMMENT '当日已付款金额',
  `stat_date` date DEFAULT NULL COMMENT '统计日期(YYYY-MM-DD)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1-启用 0-停用',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `support_collection` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持代收',
  `support_disbursement` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持代付',
  `daily_max_receipt` decimal(12,2) DEFAULT '0.00' COMMENT '单日最大收款限额',
  `daily_max_payment` decimal(12,2) DEFAULT '0.00' COMMENT '单日最大付款限额',
  `daily_max_receipt_count` int(10) unsigned DEFAULT '0' COMMENT '单日最大收款次数',
  `daily_max_payment_count` int(10) unsigned DEFAULT '0' COMMENT '单日最大付款次数',
  `max_receipt_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最大收款限额',
  `max_payment_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最大付款限额',
  `min_receipt_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最小收款限额',
  `min_payment_per_txn` decimal(12,2) DEFAULT '0.00' COMMENT '单笔最小付款限额',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_channel_merchant` (`channel_id`,`merchant_id`),
  KEY `idx_support_collection` (`support_collection`),
  KEY `idx_support_disbursement` (`support_disbursement`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='渠道账户表';

-- ----------------------------
-- Table structure for channel_account_daily_stats
-- ----------------------------
DROP TABLE IF EXISTS `channel_account_daily_stats`;
CREATE TABLE `channel_account_daily_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel_account_id` bigint(20) unsigned NOT NULL COMMENT '关联channel_account.id',
  `bank_account_id` bigint(20) unsigned NOT NULL COMMENT '关联bank_account.id',
  `channel_id` bigint(20) unsigned NOT NULL COMMENT '渠道ID',
  `stat_date` date NOT NULL COMMENT '统计日期(YYYY-MM-DD)',
  `transaction_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当日交易总次数',
  `success_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功交易次数',
  `failure_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '失败交易次数',
  `receipt_amount` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '当日已收款金额',
  `payment_amount` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '当日已付款金额',
  `success_rate` decimal(5,2) DEFAULT '0.00' COMMENT '交易成功率(%)',
  `avg_process_time` int(6) DEFAULT '0' COMMENT '平均处理时间(ms)',
  `limit_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '限额状态:0正常 1部分限额 2完全限额',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_channel_bank_date` (`channel_account_id`,`bank_account_id`,`stat_date`),
  KEY `idx_channel_date` (`channel_id`,`stat_date`),
  KEY `idx_bank_account` (`bank_account_id`,`stat_date`),
  KEY `idx_channel_account` (`channel_account_id`,`stat_date`),
  KEY `idx_stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='渠道账户每日统计表';

-- ----------------------------
-- Table structure for channel_callback_record
-- ----------------------------
DROP TABLE IF EXISTS `channel_callback_record`;
CREATE TABLE `channel_callback_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `callback_id` varchar(64) NOT NULL COMMENT '回调唯一标识',
  `channel_id` bigint(20) unsigned NOT NULL COMMENT '渠道ID',
  `original_request_id` varchar(64) DEFAULT NULL COMMENT '原始请求ID(关联请求记录)',
  `callback_type` varchar(32) NOT NULL COMMENT '回调类型(如:支付结果通知、异步通知等)',
  `callback_url` varchar(512) NOT NULL COMMENT '回调请求的完整地址',
  `callback_http_method` varchar(10) NOT NULL COMMENT '回调请求的HTTP方法(GET/POST/PUT等)',
  `callback_params` text COMMENT '回调参数(JSON格式)',
  `callback_headers` text COMMENT '回调头信息(JSON格式)',
  `callback_body` text COMMENT '回调体内容',
  `callback_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '回调到达时间',
  `client_ip` varchar(50) DEFAULT NULL COMMENT '回调来源IP',
  `verification_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '验签状态: 0-未验签, 1-验签成功, 2-验签失败',
  `response_content` text COMMENT '返回给渠道的内容',
  `process_result` varchar(512) NOT NULL DEFAULT '' COMMENT '处理结果描述',
  `elapsed_time` int(11) DEFAULT NULL COMMENT '处理耗时(毫秒)',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_callback_id` (`callback_id`),
  KEY `idx_channel_id` (`channel_id`),
  KEY `idx_original_request_id` (`original_request_id`),
  KEY `idx_callback_time` (`callback_time`),
  KEY `idx_callback_http_method` (`callback_http_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='渠道回调记录表';

-- ----------------------------
-- Table structure for channel_request_record
-- ----------------------------
DROP TABLE IF EXISTS `channel_request_record`;
CREATE TABLE `channel_request_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `request_id` varchar(64) NOT NULL COMMENT '请求唯一标识',
  `channel_id` bigint(20) unsigned NOT NULL COMMENT '渠道ID',
  `api_method` varchar(128) NOT NULL COMMENT '调用的API方法说明',
  `request_url` varchar(512) NOT NULL COMMENT '完整请求地址',
  `http_method` varchar(10) NOT NULL COMMENT 'HTTP请求方法(GET/POST/PUT/DELETE等)',
  `request_params` text COMMENT '请求参数(JSON格式)',
  `request_headers` text COMMENT '请求头信息(JSON格式)',
  `request_body` text COMMENT '请求体内容',
  `request_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '请求时间',
  `http_status_code` smallint(4) DEFAULT NULL COMMENT 'HTTP响应状态码',
  `response_status` varchar(32) NOT NULL DEFAULT '' COMMENT '业务响应状态(如渠道返回的status/code字段)',
  `response_headers` text COMMENT '响应头信息(JSON格式)',
  `response_body` text COMMENT '响应体内容',
  `error_message` varchar(512) DEFAULT NULL COMMENT '错误信息',
  `response_time` datetime DEFAULT NULL COMMENT '响应时间',
  `elapsed_time` int(11) DEFAULT NULL COMMENT '耗时(毫秒)',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_request_id` (`request_id`),
  KEY `idx_channel_id` (`channel_id`),
  KEY `idx_request_time` (`request_time`),
  KEY `idx_http_status` (`http_status_code`),
  KEY `idx_api_method` (`api_method`),
  KEY `idx_response_status` (`response_status`),
  KEY `idx_http_method` (`http_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='渠道请求记录表';

-- ----------------------------
-- Table structure for collection_order
-- ----------------------------
DROP TABLE IF EXISTS `collection_order`;
CREATE TABLE `collection_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `platform_order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '平台订单号',
  `tenant_order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '下游订单号',
  `upstream_order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '上游订单号',
  `amount` decimal(12,4) NOT NULL COMMENT '订单金额',
  `payable_amount` decimal(12,4) NOT NULL COMMENT '订单应付金额',
  `paid_amount` decimal(12,4) DEFAULT NULL COMMENT '订单实付金额',
  `fixed_fee` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '固定手续费',
  `rate_fee` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '费率手续费',
  `rate_fee_amount` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '费率手续费金额',
  `total_fee` decimal(12,4) unsigned DEFAULT '0.0000' COMMENT '总手续费',
  `upstream_fee` decimal(12,4) DEFAULT NULL COMMENT '上游手续费',
  `upstream_settlement_amount` decimal(12,4) DEFAULT NULL COMMENT '上游结算金额',
  `settlement_amount` decimal(12,4) DEFAULT NULL COMMENT '租户入账金额',
  `settlement_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '入账结算类型:0-未入账 1-实付金额 2-订单金额',
  `collection_type` tinyint(2) NOT NULL COMMENT '收款类型:1-银行卡 2-UPI 3-第三方支付',
  `collection_channel_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '收款渠道ID',
  `channel_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '渠道账户ID',
  `bank_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '银行账户ID',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `expire_time` datetime DEFAULT NULL COMMENT '订单失效时间',
  `order_source` varchar(64) NOT NULL DEFAULT '' COMMENT '订单来源:APP-API 管理后台 导入',
  `recon_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '核销类型:\r\n    0-未核销 \r\n    1-自动核销 \r\n    2-人工核销 \r\n    3-接口核销 \r\n    4-机器人核销',
  `notify_url` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `notify_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '通知状态:0-未通知 1-回调中 2-通知成功 3-通知失败',
  `notify_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '回调原样返回',
  `pay_url` varchar(255) DEFAULT NULL COMMENT '收银台地址',
  `return_url` varchar(255) DEFAULT NULL COMMENT '支付成功后跳转地址',
  `tenant_id` varchar(20) NOT NULL COMMENT '租户编号',
  `app_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `payer_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款方名称',
  `payer_account` varchar(100) NOT NULL DEFAULT '' COMMENT '收款账号',
  `payer_bank` varchar(100) NOT NULL DEFAULT '' COMMENT '收款方银行',
  `payer_ifsc` varchar(20) NOT NULL DEFAULT '' COMMENT '收款方IFSC代码',
  `payer_upi` varchar(100) NOT NULL DEFAULT '' COMMENT '收款方UPI账号',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '订单描述',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '订单状态:\r\n    0-创建 10-处理中 20-成功 30-挂起 40-失败 \r\n    41-已取消 43-已失效 44-已退款 ',
  `channel_transaction_no` varchar(50) NOT NULL DEFAULT '' COMMENT '渠道交易号',
  `error_code` varchar(20) NOT NULL DEFAULT '' COMMENT '错误代码',
  `error_message` text COMMENT '错误信息',
  `request_id` varchar(64) DEFAULT NULL COMMENT '关联API请求ID',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `payment_proof_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支付凭证照片',
  `platform_transaction_no` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '平台交易流水号',
  `utr` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unique Transaction Reference',
  `customer_submitted_utr` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户提交的UTR',
  `settlement_delay_mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '入账类型(1:D0 2:D1 3:T0)',
  `settlement_delay_days` tinyint(2) NOT NULL DEFAULT '0' COMMENT '入账延迟天数（自然日）',
  `transaction_voucher_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '核销凭证id',
  `cancelled_at` datetime DEFAULT NULL COMMENT '取消时间',
  `cancelled_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '取消管理员',
  `customer_cancelled_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '取消客户',
  `customer_created_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '客户创建ID',
  `pay_time_hour` varchar(10) GENERATED ALWAYS AS (date_format(`pay_time`,'%Y%m%d%H')) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_platform_order_no` (`platform_order_no`),
  UNIQUE KEY `uniq_merchant_order` (`tenant_id`,`tenant_order_no`),
  UNIQUE KEY `uniq_request_id` (`request_id`) COMMENT '防止重复订单',
  UNIQUE KEY `platform_transaction_no` (`platform_transaction_no`),
  UNIQUE KEY `customer_submitted_utr` (`customer_submitted_utr`),
  KEY `idx_tenant_app` (`tenant_id`,`app_id`),
  KEY `idx_status_expire` (`status`,`expire_time`),
  KEY `idx_payer_account` (`payer_account`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_pay_time` (`pay_time`) COMMENT '支付时间索引',
  KEY `idx_recon_type` (`recon_type`) COMMENT '核销类型索引',
  KEY `idx_platform_transaction` (`platform_transaction_no`),
  KEY `idx_utr` (`utr`),
  KEY `idx_channel_account_id` (`channel_account_id`),
  KEY `idx_bank_account_id` (`bank_account_id`),
  KEY `idx_settlement_mode` (`settlement_delay_mode`),
  KEY `utr` (`utr`) USING BTREE,
  KEY `tenant_id` (`tenant_id`),
  KEY `idx_tenant_id_created_at` (`tenant_id`,`created_at`),
  KEY `idx_pay_time_hour` (`pay_time_hour`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COMMENT='代收订单表';

-- ----------------------------
-- Table structure for collection_order_status_records
-- ----------------------------
DROP TABLE IF EXISTS `collection_order_status_records`;
CREATE TABLE `collection_order_status_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态',
  `desc_cn` text COMMENT '中文信息',
  `desc_en` text COMMENT '英文信息',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='收款订单状态变更记录表';

-- ----------------------------
-- Table structure for data_permission_policy
-- ----------------------------
DROP TABLE IF EXISTS `data_permission_policy`;
CREATE TABLE `data_permission_policy` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户ID（与角色二选一）',
  `position_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '岗位ID（与用户二选一）',
  `policy_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '策略类型（DEPT_SELF, DEPT_TREE, ALL, SELF, CUSTOM_DEPT, CUSTOM_FUNC）',
  `is_default` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否默认策略（默认值：true）',
  `value` json DEFAULT NULL COMMENT '策略值',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_position_id` (`position_id`),
  KEY `idx_policy_type` (`policy_type`),
  KEY `idx_user_policy` (`user_id`,`policy_type`,`deleted_at`),
  KEY `idx_position_policy` (`position_id`,`policy_type`,`deleted_at`),
  KEY `idx_user_default` (`user_id`,`is_default`,`deleted_at`),
  KEY `idx_position_default` (`position_id`,`is_default`,`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据权限策略';

-- ----------------------------
-- Table structure for department
-- ----------------------------
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '部门名称',
  `parent_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '父级部门ID',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='部门表';

-- ----------------------------
-- Table structure for dept_leader
-- ----------------------------
DROP TABLE IF EXISTS `dept_leader`;
CREATE TABLE `dept_leader` (
  `dept_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='部门领导表';

-- ----------------------------
-- Table structure for disbursement_order
-- ----------------------------
DROP TABLE IF EXISTS `disbursement_order`;
CREATE TABLE `disbursement_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `platform_order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '平台订单号',
  `tenant_order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '下游订单号',
  `upstream_order_no` varchar(64) DEFAULT NULL COMMENT '上游订单号',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `order_source` varchar(64) NOT NULL DEFAULT '' COMMENT '订单来源:App-API 管理后台 导入',
  `disbursement_channel_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '代付渠道D',
  `channel_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '渠道类型：1-银行 2-上游第三方',
  `bank_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '代付银行卡ID',
  `channel_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '渠道账号ID',
  `amount` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '订单金额',
  `fixed_fee` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '固定手续费',
  `rate_fee` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '费率手续费',
  `rate_fee_amount` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '费率手续费金额',
  `total_fee` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '总手续费',
  `settlement_amount` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '租户扣减金额',
  `upstream_fee` decimal(12,4) DEFAULT '0.0000' COMMENT '上游手续费',
  `upstream_settlement_amount` decimal(12,4) DEFAULT '0.0000' COMMENT '上游结算金额',
  `payment_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '付款类型:1-银行卡 2-UPI',
  `payee_bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人银行名称',
  `payee_bank_code` varchar(20) NOT NULL DEFAULT '' COMMENT '收款人银行编码ifsc',
  `payee_account_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人账户姓名',
  `payee_account_no` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人银行卡号',
  `payee_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '收款人电话号码',
  `payee_upi` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人UPI账号',
  `utr` varchar(50) NOT NULL DEFAULT '' COMMENT '实际交易的凭证/UTR',
  `tenant_id` varchar(20) NOT NULL COMMENT '租户编号',
  `app_id` bigint(20) NOT NULL COMMENT '应用ID',
  `description` varchar(255) DEFAULT NULL COMMENT '订单描述',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '订单状态:\n    0-创建中 10-待支付 11-待回填 20-成功 30-挂起 \n    40-失败 41-已取消 43-已失效 44-已退款',
  `expire_time` datetime DEFAULT NULL COMMENT '订单失效时间',
  `notify_url` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `notify_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '通知状态:0-未通知 1-回调中 2-通知成功 3-通知失败',
  `channel_transaction_no` varchar(50) DEFAULT NULL COMMENT '渠道交易号',
  `error_code` varchar(20) DEFAULT NULL COMMENT '错误代码',
  `error_message` text COMMENT '错误信息',
  `request_id` varchar(64) DEFAULT NULL COMMENT '关联API请求ID',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `cancelled_at` datetime DEFAULT NULL COMMENT '取消时间',
  `cancelled_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '取消管理员',
  `transaction_voucher_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '核销凭证ID',
  `down_bill_template_id` varchar(50) NOT NULL DEFAULT '' COMMENT '付款账单模板',
  `bank_disbursement_download_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '银行支付账单下载ID',
  `customer_created_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '客户端创建ID',
  `customer_cancelled_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '客户端取消ID',
  `transaction_record_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '交易ID',
  `notify_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '回调原样返回',
  `refund_at` datetime DEFAULT NULL COMMENT '退款时间',
  `refund_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '退款原因',
  `payment_voucher_image` varchar(255) NOT NULL DEFAULT '' COMMENT '支付凭证图片',
  `pay_time_hour` varchar(10) GENERATED ALWAYS AS (date_format(`pay_time`,'%Y%m%d%H')) STORED,
  `platform_transaction_no` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '平台交易流水号',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_platform_order_no` (`platform_order_no`),
  UNIQUE KEY `uniq_merchant_order` (`tenant_id`,`tenant_order_no`),
  UNIQUE KEY `uniq_request_id` (`request_id`) COMMENT '防止重复订单',
  KEY `idx_tenant_app` (`tenant_id`,`app_id`),
  KEY `idx_status_expire` (`status`,`expire_time`),
  KEY `idx_payee_account` (`payee_account_no`),
  KEY `idx_utr` (`utr`) COMMENT 'UTR索引',
  KEY `idx_created_at` (`created_at`),
  KEY `idx_pay_time` (`pay_time`) COMMENT '支付时间索引',
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_tenant_id_created_at` (`tenant_id`,`created_at`),
  KEY `idx_pay_time_hour` (`pay_time_hour`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COMMENT='代付订单表';

-- ----------------------------
-- Table structure for disbursement_order_status_records
-- ----------------------------
DROP TABLE IF EXISTS `disbursement_order_status_records`;
CREATE TABLE `disbursement_order_status_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态',
  `desc_cn` text COMMENT '中文信息',
  `desc_en` text COMMENT '英文信息',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COMMENT='付款订单状态变更记录表';

-- ----------------------------
-- Table structure for disbursement_order_verification_queue
-- ----------------------------
DROP TABLE IF EXISTS `disbursement_order_verification_queue`;
CREATE TABLE `disbursement_order_verification_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `platform_order_no` varchar(64) NOT NULL COMMENT '平台订单号',
  `amount` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '支付金额',
  `utr` varchar(32) NOT NULL DEFAULT '' COMMENT 'UTR',
  `payment_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付状态:0未支付1支付中2支付成功3支付失败',
  `order_data` json NOT NULL COMMENT '订单数据',
  `process_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '处理状态:\r\n    0-待处理 1-处理中 2-成功 3-失败',
  `retry_count` tinyint(4) NOT NULL DEFAULT '0' COMMENT '重试次数',
  `next_retry_time` datetime DEFAULT NULL COMMENT '下次重试时间',
  `rejection_reason` text COMMENT '拒绝原因',
  `created_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_retry` (`process_status`,`next_retry_time`),
  KEY `idx_platform_order_no` (`platform_order_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='代付订单核销处理队列';

-- ----------------------------
-- Table structure for disbursement_order_upstream_create_queue
-- ----------------------------
DROP TABLE IF EXISTS `disbursement_order_upstream_create_queue`;
CREATE TABLE `disbursement_order_upstream_create_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `platform_order_no` varchar(32) NOT NULL COMMENT '平台订单号',
  `disbursement_order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '代付订单ID',
  `tenant_id` varchar(20) NOT NULL COMMENT '租户编号',
  `app_id` bigint(20) NOT NULL COMMENT '应用ID',
  `channel_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '渠道账号ID',
  `amount` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '订单金额',
  `payee_bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人银行名称',
  `payee_bank_code` varchar(20) NOT NULL DEFAULT '' COMMENT '收款人银行编码',
  `payee_account_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人账户姓名',
  `payee_account_no` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人银行卡号',
  `payee_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '收款人电话号码',
  `payee_upi` varchar(100) NOT NULL DEFAULT '' COMMENT '收款人UPI账号',
  `payment_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '付款类型:1-银行卡 2-UPI',
  `order_data` json NOT NULL COMMENT '订单完整数据',
  `process_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '处理状态:0-待处理 1-处理中 2-成功 3-失败',
  `retry_count` tinyint(4) NOT NULL DEFAULT '0' COMMENT '重试次数',
  `max_retry_count` tinyint(4) NOT NULL DEFAULT '3' COMMENT '最大重试次数',
  `next_retry_time` datetime DEFAULT NULL COMMENT '下次重试时间',
  `upstream_order_no` varchar(64) DEFAULT NULL COMMENT '上游订单号',
  `upstream_response` text COMMENT '上游返回数据',
  `error_code` varchar(20) DEFAULT NULL COMMENT '错误代码',
  `error_message` text COMMENT '错误信息',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL COMMENT '处理完成时间',
  `lock_version` int(11) NOT NULL DEFAULT '0' COMMENT '乐观锁版本号',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_platform_order_no` (`platform_order_no`),
  KEY `idx_disbursement_order_id` (`disbursement_order_id`),
  KEY `idx_tenant_app` (`tenant_id`,`app_id`),
  KEY `idx_status_retry` (`process_status`,`next_retry_time`),
  KEY `idx_channel_account` (`channel_account_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='代付订单上游创建队列';

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `parent_id` bigint(20) unsigned NOT NULL COMMENT '父ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '菜单名称',
  `meta` json DEFAULT NULL COMMENT '附加属性',
  `path` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `component` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
  `redirect` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '重定向地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:1=正常,2=停用',
  `sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建者',
  `updated_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新者',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `remark` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=290 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='菜单信息表';

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for position
-- ----------------------------
DROP TABLE IF EXISTS `position`;
CREATE TABLE `position` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '岗位名称',
  `dept_id` bigint(20) NOT NULL COMMENT '部门ID',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='岗位表';

-- ----------------------------
-- Table structure for recycle_bin
-- ----------------------------
DROP TABLE IF EXISTS `recycle_bin`;
CREATE TABLE `recycle_bin` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'ID',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '000000' COMMENT '租户编号',
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回收的数据',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表',
  `table_prefix` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '表前缀',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '是否已还原(1已还原，2未还原)',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '操作者IP',
  `operate_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '操作管理员',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `recycle_bin_tenant_id_index` (`tenant_id`),
  KEY `recycle_bin_table_name_index` (`table_name`),
  KEY `recycle_bin_operate_by_created_at_index` (`operate_by`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='数据回收记录表';

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色代码',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:1=正常,2=停用',
  `sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建者',
  `updated_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新者',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色信息表';

-- ----------------------------
-- Table structure for role_belongs_menu
-- ----------------------------
DROP TABLE IF EXISTS `role_belongs_menu`;
CREATE TABLE `role_belongs_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) NOT NULL COMMENT '角色id',
  `menu_id` bigint(20) NOT NULL COMMENT '菜单id',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id_index` (`role_id`),
  KEY `menu_id_index` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for rules
-- ----------------------------
DROP TABLE IF EXISTS `rules`;
CREATE TABLE `rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ptype` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v0` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '组id',
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置键名',
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '配置值',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '配置名称',
  `input_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '数据输入类型',
  `config_select_data` json DEFAULT NULL COMMENT '配置选项数据',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) DEFAULT NULL COMMENT '创建者',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新者',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_setting_config_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for system_config_group
-- ----------------------------
DROP TABLE IF EXISTS `system_config_group`;
CREATE TABLE `system_config_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置组名称',
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置组标识',
  `icon` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '配置组图标',
  `created_by` bigint(20) DEFAULT NULL COMMENT '创建者',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新者',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_unique_index` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for telegram_command_message_record
-- ----------------------------
DROP TABLE IF EXISTS `telegram_command_message_record`;
CREATE TABLE `telegram_command_message_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户账号',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `chat_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '聊天群id',
  `chat_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '群名称',
  `message_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '消息ID',
  `command` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '命令',
  `original_message` text COLLATE utf8mb4_unicode_ci COMMENT '原始信息',
  `response_message` text COLLATE utf8mb4_unicode_ci COMMENT '返回信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1待处理 2已处理 3处理失败)',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_message_id` (`message_id`),
  KEY `chat_id_idx_message_id` (`chat_id`,`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=514 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='telegram命令消息记录表';

-- ----------------------------
-- Table structure for tenant
-- ----------------------------
DROP TABLE IF EXISTS `tenant`;
CREATE TABLE `tenant` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '租户编号',
  `contact_user_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `company_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业名称',
  `license_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业代码',
  `address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '地址',
  `intro` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业简介',
  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '域名',
  `user_num_limit` int(11) NOT NULL DEFAULT '-1' COMMENT '用户数量（-1不限制）',
  `app_num_limit` int(11) DEFAULT '-1' COMMENT '应用数量（-1不限制）',
  `is_enabled` tinyint(1) DEFAULT '2' COMMENT '接单启用状态(1正常 0停用)',
  `created_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建管理员',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `expired_at` datetime DEFAULT NULL COMMENT '过期时间',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '更新者',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `safe_level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '安全等级(0-99)',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除者',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `settlement_delay_mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '入账类型(1:D0 2:D 3:T)',
  `settlement_delay_days` tinyint(2) NOT NULL DEFAULT '0' COMMENT '入账延迟天数',
  `auto_transfer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动划扣(1是 0否)',
  `receipt_fee_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]' COMMENT '收款手续费类型(1固定 2费率)',
  `receipt_fixed_fee` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT '收款固定手续费金额',
  `receipt_fee_rate` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT '收款手续费费率(%)',
  `payment_fee_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]' COMMENT '付款手续费类型(1固定 2费率)',
  `payment_fixed_fee` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT '付款固定手续费金额',
  `payment_fee_rate` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT '付款手续费费率(%)',
  `is_receipt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否收款(1是 0否)',
  `is_payment` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否付款(1是 0否)',
  `receipt_min_amount` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '单次收款最小金额',
  `receipt_max_amount` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '单次收款最大金额',
  `payment_min_amount` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '单次付款最小金额',
  `payment_max_amount` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '单次付款最大金额',
  `receipt_settlement_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '收款结算方式(1实际金额 2订单金额)',
  `upstream_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用上游第三方收款(1是 0否)',
  `upstream_items` json DEFAULT NULL COMMENT '上游第三方收款顺序',
  `float_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用金额浮动(1是 0否)',
  `float_range` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[0,0]' COMMENT '金额浮动区间(格式：-5,5)',
  `notify_range` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[0,0]' COMMENT '下游通知金额区间(格式：100,1000)',
  `auto_assign_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用自动分配(1是 0否)',
  `receipt_expire_minutes` int(11) NOT NULL DEFAULT '30' COMMENT '收款订单失效时间(分钟)',
  `payment_expire_minutes` int(11) NOT NULL DEFAULT '30' COMMENT '付款订单失效时间(分钟)',
  `reconcile_retain_minutes` int(11) NOT NULL DEFAULT '1440' COMMENT '浮动金额缓存延时(分钟)',
  `bill_delay_minutes` int(11) NOT NULL DEFAULT '60' COMMENT '账单待处理延时(分钟)',
  `card_acquire_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '银行卡获取方式(1随机 2依次 3轮询)',
  `auto_verify_fail_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '自动核销失败比例(%)',
  `payment_assign_items` json DEFAULT NULL COMMENT '付款分配项(JSON格式)',
  `collection_use_method` json DEFAULT NULL COMMENT '收款使用方法1公户 2上游',
  `tg_chat_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'telegram bot chat id',
  `cashier_template` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '收银台模板',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `tenant_id_unique_index` (`tenant_id`) USING BTREE,
  KEY `idx_status_filter` (`is_enabled`,`expired_at`) USING BTREE COMMENT '状态+过期时间联合索引',
  KEY `idx_safe_level` (`safe_level`) USING BTREE COMMENT '安全等级快速筛选',
  KEY `idx_settlement_type` (`settlement_delay_mode`) USING BTREE COMMENT '入账类型查询',
  KEY `idx_auto_transfer` (`auto_transfer`) USING BTREE COMMENT '自动划扣查询',
  KEY `idx_created_range` (`created_at`) USING BTREE COMMENT '创建时间范围查询',
  KEY `idx_expired_range` (`expired_at`) USING BTREE COMMENT '过期时间范围查询',
  KEY `idx_receipt_switch` (`is_receipt`,`is_enabled`),
  KEY `idx_payment_switch` (`is_payment`,`is_enabled`),
  KEY `idx_tg_chat_id` (`tg_chat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='租户表';

-- ----------------------------
-- Table structure for tenant_account
-- ----------------------------
DROP TABLE IF EXISTS `tenant_account`;
CREATE TABLE `tenant_account` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` varchar(20) NOT NULL DEFAULT '' COMMENT '租户编号',
  `account_id` varchar(22) NOT NULL COMMENT '自定义账户ID（格式：租户ID_账户类型）',
  `balance_available` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '可用余额',
  `balance_frozen` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '冻结金额',
  `account_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '账户类型:1-收款账户 2-付款账户',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '乐观锁版本',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_account_id` (`account_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='租户账户表';

-- ----------------------------
-- Table structure for tenant_account_record
-- ----------------------------
DROP TABLE IF EXISTS `tenant_account_record`;
CREATE TABLE `tenant_account_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '租户编号',
  `tenant_account_id` bigint(20) NOT NULL COMMENT '关联租户账户ID',
  `account_id` varchar(22) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '账户ID',
  `account_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '账户类型:1-收款账户 2-付款账户',
  `change_amount` decimal(10,4) NOT NULL COMMENT '变更金额（正：增加，负：减少）',
  `balance_available_before` decimal(10,4) NOT NULL COMMENT '变更前余额',
  `balance_available_after` decimal(10,4) NOT NULL COMMENT '变更后余额',
  `balance_frozen_before` decimal(10,4) DEFAULT NULL COMMENT '变更前冻结金额',
  `balance_frozen_after` decimal(10,4) DEFAULT NULL COMMENT '变更后冻结金额',
  `change_type` tinyint(2) NOT NULL COMMENT '变更类型：1-订单交易 2-订单退款 3-人工加帐 4-人工减帐 5-冻结 6-解冻 7-转入 8-转出 9-冲正 10-调整差错',
  `transaction_no` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关联交易流水号',
  `created_at` datetime NOT NULL COMMENT '记录创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transaction_no` (`transaction_no`),
  KEY `idx_transaction_no` (`transaction_no`),
  KEY `idx_tenant_type_time` (`tenant_id`,`account_type`,`created_at`),
  KEY `idx_change_type` (`change_type`),
  KEY `idx_account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户账户变更日志表';

-- ----------------------------
-- Table structure for tenant_api_interface
-- ----------------------------
DROP TABLE IF EXISTS `tenant_api_interface`;
CREATE TABLE `tenant_api_interface` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `api_name` varchar(100) NOT NULL COMMENT '接口名称',
  `api_uri` varchar(255) NOT NULL COMMENT '接口URI',
  `http_method` varchar(5) NOT NULL COMMENT '请求方式:GET,POST',
  `request_params` json NOT NULL COMMENT '请求参数说明',
  `request_params_en` json NOT NULL COMMENT '英文请求参数说明',
  `request_example` json NOT NULL COMMENT '请求参数示例',
  `request_example_en` json NOT NULL COMMENT '英文请求参数示例',
  `response_params` json NOT NULL COMMENT '响应参数说明',
  `response_params_en` json NOT NULL COMMENT '英文响应参数说明',
  `response_example` json NOT NULL COMMENT '响应参数示例',
  `response_example_en` json NOT NULL COMMENT '英文响应参数示例',
  `description` text NOT NULL COMMENT '接口描述',
  `description_en` text NOT NULL COMMENT '接口英文描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1-启用 0-停用',
  `rate_limit` int(11) NOT NULL DEFAULT '100' COMMENT '每秒请求限制',
  `auth_mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '认证模式 (0不需要认证 1简易签名 2复杂)',
  `created_by` bigint(20) NOT NULL COMMENT '创建人',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_api_uri_method` (`api_uri`,`http_method`),
  UNIQUE KEY `idx_api_name` (`api_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='开放API接口表';

-- ----------------------------
-- Table structure for tenant_app
-- ----------------------------
DROP TABLE IF EXISTS `tenant_app`;
CREATE TABLE `tenant_app` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000' COMMENT '租户编号',
  `app_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '应用名称',
  `app_key` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '应用ID',
  `app_secret` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '应用密钥',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 (1正常 2停用)',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '应用介绍',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建者',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '更新者',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除者',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `tenant_app_app_key_index` (`app_key`) USING BTREE,
  KEY `tenant_app_tenant_id_index` (`tenant_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='租户应用表';

-- ----------------------------
-- Table structure for tenant_app_log
-- ----------------------------
DROP TABLE IF EXISTS `tenant_app_log`;
CREATE TABLE `tenant_app_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000' COMMENT '租户编号',
  `app_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '接口ID',
  `app_key` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `access_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口访问路径',
  `request_id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求id',
  `request_data` text COLLATE utf8mb4_unicode_ci COMMENT '请求数据',
  `response_code` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '响应状态码',
  `response_success` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 (1成功 2失败)',
  `response_message` text COLLATE utf8mb4_unicode_ci COMMENT '响应信息',
  `response_data` text COLLATE utf8mb4_unicode_ci COMMENT '响应数据',
  `ip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '访问IP地址',
  `ip_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP所属地',
  `access_time` datetime DEFAULT NULL COMMENT '访问时间',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `duration` int(10) unsigned NOT NULL COMMENT '毫秒',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uidx_request_id` (`request_id`),
  KEY `tenant_app_log_tenant_id_index` (`tenant_id`) USING BTREE,
  KEY `tenant_app_log_access_path_index` (`access_path`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6837611 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='租户接口访问日志表';

-- ----------------------------
-- Table structure for tenant_config
-- ----------------------------
DROP TABLE IF EXISTS `tenant_config`;
CREATE TABLE `tenant_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `tenant_id` varchar(20) NOT NULL DEFAULT '' COMMENT '租户编号',
  `group_code` varchar(64) NOT NULL DEFAULT '' COMMENT '分组编码',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '唯一编码',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '配置名称',
  `content` longtext COMMENT '配置内容',
  `enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `intro` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '介绍说明',
  `option` json DEFAULT NULL COMMENT '备用选项',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建者',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '更新者',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_tenant_id_code` (`tenant_id`,`code`),
  KEY `idx_config_code` (`code`) USING BTREE,
  KEY `idx_config_group_code` (`group_code`) USING BTREE,
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='租户配置';

-- ----------------------------
-- Table structure for tenant_notification_queue
-- ----------------------------
DROP TABLE IF EXISTS `tenant_notification_queue`;
CREATE TABLE `tenant_notification_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(20) NOT NULL COMMENT '租户编号',
  `app_id` bigint(20) NOT NULL COMMENT '应用ID',
  `account_type` tinyint(2) NOT NULL COMMENT '账户变动类型（继承tenant_account类型1-收款账户 2-付款账户）',
  `collection_order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '收款订单ID',
  `disbursement_order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '付款订单ID',
  `notification_type` tinyint(2) NOT NULL COMMENT '通知类型:1-系统通知 2-订单通知 3-账单通知',
  `notification_url` varchar(255) NOT NULL COMMENT '通知地址',
  `request_method` varchar(10) NOT NULL DEFAULT 'GET' COMMENT '请求方式',
  `request_data` text NOT NULL COMMENT '请求数据',
  `execute_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '执行状态:0-待执行 1-执行中 2-成功 3-失败',
  `execute_count` int(10) NOT NULL DEFAULT '0' COMMENT '执行次数',
  `next_execute_time` datetime DEFAULT NULL COMMENT '下次执行时间',
  `last_execute_time` datetime DEFAULT NULL COMMENT '最后执行时间',
  `error_message` text COMMENT '错误信息',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `max_retry_count` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '最大尝试次数',
  `lock_version` int(11) NOT NULL DEFAULT '0' COMMENT '乐观锁版本号',
  PRIMARY KEY (`id`),
  KEY `idx_tenant_app_id` (`tenant_id`,`app_id`),
  KEY `idx_collection_orderid` (`collection_order_id`),
  KEY `idx_disbursement_order_id` (`disbursement_order_id`),
  KEY `idx_notification_type` (`notification_type`),
  KEY `idx_status_time` (`execute_status`,`next_execute_time`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='回调队列执行状态表';

-- ----------------------------
-- Table structure for tenant_notification_record
-- ----------------------------
DROP TABLE IF EXISTS `tenant_notification_record`;
CREATE TABLE `tenant_notification_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` bigint(20) unsigned DEFAULT NULL COMMENT '队列ID',
  `tenant_id` varchar(20) NOT NULL COMMENT '租户编号',
  `app_id` bigint(20) NOT NULL COMMENT '应用ID',
  `account_type` tinyint(2) NOT NULL COMMENT '账户变动类型（继承tenant_account类型1-收款账户 2-付款账户）',
  `collection_order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '收款订单ID',
  `disbursement_order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '付款订单ID',
  `notification_type` tinyint(2) NOT NULL COMMENT '通知类型:1-系统通知 2-订单通知  3-账单通知',
  `notification_url` varchar(255) NOT NULL COMMENT '通知地址',
  `request_method` varchar(10) NOT NULL COMMENT '请求方式',
  `request_data` text NOT NULL COMMENT '请求数据',
  `response_status` int(11) DEFAULT NULL COMMENT '响应状态码',
  `response_data` text COMMENT '响应数据',
  `execute_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '重试次数',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '回调状态:0-失败 1-成功 ',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_app_id` (`tenant_id`,`app_id`),
  KEY `idx_collection_orderid` (`collection_order_id`),
  KEY `idx_disbursement_order_id` (`disbursement_order_id`),
  KEY `idx_notification_type` (`notification_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COMMENT='回调通知记录表';

-- ----------------------------
-- Table structure for tenant_user
-- ----------------------------
DROP TABLE IF EXISTS `tenant_user`;
CREATE TABLE `tenant_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000' COMMENT '租户编号',
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `last_login_ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '最后登陆IP',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登陆时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1正常 2停用)',
  `is_enabled_google` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'google验证(1正常 2停用)',
  `google_secret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Google验证密钥',
  `is_bind_google` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已绑定Google验证(1yes 2no)',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建者',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '更新者',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除者',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `ip_whitelist` text COLLATE utf8mb4_unicode_ci COMMENT 'IP白名单',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `backend_setting` json DEFAULT NULL COMMENT '客户端配置',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_username` (`username`) USING BTREE,
  KEY `tenant_app_tenant_id_index` (`tenant_id`) USING BTREE,
  KEY `idx_phone` (`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='租户用户表';

-- ----------------------------
-- Table structure for tenant_user_login_log
-- ----------------------------
DROP TABLE IF EXISTS `tenant_user_login_log`;
CREATE TABLE `tenant_user_login_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000' COMMENT '租户编号',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '登录IP地址',
  `os` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '操作系统',
  `browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '浏览器',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '登录状态 (1成功 2失败)',
  `message` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '提示消息',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `tenant_user_login_log_tenant_id_index` (`tenant_id`),
  KEY `tenant_user_login_log_username_index` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户登录日志表';

-- ----------------------------
-- Table structure for tenant_user_operation_log
-- ----------------------------
DROP TABLE IF EXISTS `tenant_user_operation_log`;
CREATE TABLE `tenant_user_operation_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000' COMMENT '租户编号',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `method` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求方式',
  `router` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求路由',
  `service_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '业务名称',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '请求IP地址',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `request_params` longtext COLLATE utf8mb4_unicode_ci COMMENT '请求参数',
  `response_status` int(11) DEFAULT NULL COMMENT '响应状态码',
  `is_success` tinyint(1) DEFAULT '1' COMMENT '操作是否成功(1:成功,0:失败)',
  `response_data` longtext COLLATE utf8mb4_unicode_ci COMMENT '响应数据',
  `operator_id` bigint(20) unsigned DEFAULT '0' COMMENT '操作者ID',
  `request_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'uuid',
  `request_duration` bigint(20) unsigned DEFAULT NULL COMMENT '请求耗时(毫秒)',
  PRIMARY KEY (`id`),
  KEY `tenant_user_operation_log_tenant_id_index` (`tenant_id`),
  KEY `tenant_user_operation_log_username_index` (`username`),
  KEY `tenant_user_operation_log_created_at_index` (`created_at`),
  KEY `tenant_user_operation_log_service_name_index` (`service_name`),
  KEY `tenant_user_operation_log_operator_id_index` (`operator_id`),
  KEY `tenant_user_operation_log_operator_id_created_at_index` (`operator_id`,`created_at`),
  KEY `tenant_user_operation_log_created_at_service_name_index` (`created_at`,`service_name`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- ----------------------------
-- Table structure for transaction_parsing_log
-- ----------------------------
DROP TABLE IF EXISTS `transaction_parsing_log`;
CREATE TABLE `transaction_parsing_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `raw_data_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '原始数据ID',
  `rule_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '规则ID',
  `rule_text` text COLLATE utf8mb4_unicode_ci COMMENT '规则内容',
  `variable_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '记录匹配变量名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：1解析成功 2失败或部分失败',
  `voucher_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '凭证ID',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL,
  `fail_msg` text COLLATE utf8mb4_unicode_ci COMMENT '失败原因说明',
  `fail_msg_en` text COLLATE utf8mb4_unicode_ci COMMENT '失败原因说明引文',
  PRIMARY KEY (`id`),
  KEY `idx_raw_data_id` (`raw_data_id`),
  KEY `idx_rule_id` (`rule_id`),
  KEY `idx_status` (`status`),
  KEY `idx_voucher_id` (`voucher_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_rule_id_status` (`rule_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='原始凭证解析记录表';

-- ----------------------------
-- Table structure for transaction_parsing_rules
-- ----------------------------
DROP TABLE IF EXISTS `transaction_parsing_rules`;
CREATE TABLE `transaction_parsing_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道ID',
  `regex` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '正则表达式',
  `variable_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提取变量名',
  `example_data` text COLLATE utf8mb4_unicode_ci COMMENT '示例数据',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：1启用 0禁用',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_channel_id` (`channel_id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='交易解析规则表';

-- ----------------------------
-- Table structure for transaction_queue_status
-- ----------------------------
DROP TABLE IF EXISTS `transaction_queue_status`;
CREATE TABLE `transaction_queue_status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `transaction_no` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关联交易流水号',
  `transaction_type` smallint(3) NOT NULL COMMENT '冗余业务交易类型（便于按类型调度）',
  `process_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态:0-处理中 1-成功 2-失败 3-挂起',
  `scheduled_execute_time` datetime DEFAULT NULL COMMENT '执行执行时间',
  `next_retry_time` datetime DEFAULT NULL COMMENT '下次重试时间',
  `retry_count` int(11) NOT NULL DEFAULT '0' COMMENT '重试次数',
  `lock_version` int(11) NOT NULL DEFAULT '0' COMMENT '乐观锁版本号',
  `error_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '错误代码',
  `error_detail` text COLLATE utf8mb4_unicode_ci COMMENT '错误详情',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transaction_no` (`transaction_no`),
  KEY `idx_type_status` (`transaction_type`,`process_status`),
  KEY `idx_schedule_time` (`scheduled_execute_time`),
  KEY `idx_retry_schedule` (`next_retry_time`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交易队列状态表';

-- ----------------------------
-- Table structure for transaction_raw_data
-- ----------------------------
DROP TABLE IF EXISTS `transaction_raw_data`;
CREATE TABLE `transaction_raw_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '哈希值',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '内容',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '来源',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态：0未解析 1解析成功 2解析失败',
  `repeat_count` int(11) NOT NULL DEFAULT '1' COMMENT '计数',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `channel_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '渠道ID',
  `bank_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '银行账户ID',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_hash` (`hash`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE,
  KEY `idx_updated_at` (`updated_at`) USING BTREE,
  KEY `idx_source` (`source`),
  KEY `idx_channel_id` (`channel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='交易原始数据表';

-- ----------------------------
-- Table structure for transaction_record
-- ----------------------------
DROP TABLE IF EXISTS `transaction_record`;
CREATE TABLE `transaction_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `transaction_no` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '全局唯一交易流水号',
  `tenant_account_id` bigint(20) NOT NULL COMMENT '关联租户账户ID',
  `account_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '冗余账号ID',
  `tenant_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '冗余租户编号',
  `amount` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '交易金额（正：收入，负：支出）',
  `fee_amount` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '手续费金额',
  `net_amount` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '净额（变化值）',
  `account_type` tinyint(2) NOT NULL COMMENT '账户变动类型（继承tenant_account类型1-收款账户 2-付款账户）',
  `transaction_type` smallint(3) NOT NULL COMMENT '业务交易类型：10-订单交易 11-订单退款 20-人工加帐 21-人工减帐 23-冻结 24-解冻 30-收转付 31-付转收 40-冲正 41-调整差错',
  `settlement_delay_mode` tinyint(2) NOT NULL DEFAULT '1' COMMENT '延迟模式:1-D0(立即) 2-D(自然日) 3-T(工作日)',
  `expected_settlement_time` datetime DEFAULT NULL COMMENT '预计结算时间',
  `settlement_delay_days` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '延迟天数（D/T）',
  `holiday_adjustment` tinyint(1) NOT NULL DEFAULT '1' COMMENT '节假日调整:0-不调整 1-顺延 2-提前',
  `actual_settlement_time` datetime DEFAULT NULL COMMENT '实际结算时间',
  `counterparty` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '交易对手方标识',
  `order_no` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关联业务订单号',
  `order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '关联业务订单ID',
  `ref_transaction_no` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关联原交易流水号',
  `transaction_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '交易状态:0-等待结算 1-处理中 2-撤销 3-成功 4-失败',
  `failed_msg` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '失败错误信息',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易备注',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transaction_no` (`transaction_no`),
  KEY `idx_tenant_account` (`tenant_account_id`),
  KEY `idx_tenant_transaction` (`tenant_id`,`created_at`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_ref_transaction` (`ref_transaction_no`),
  KEY `idx_settlement_time` (`expected_settlement_time`),
  KEY `idx_account_status` (`tenant_account_id`,`transaction_status`,`transaction_type`),
  KEY `idx_tenant_type_time` (`tenant_id`,`transaction_type`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=377 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交易记录表';

-- ----------------------------
-- Table structure for transaction_voucher
-- ----------------------------
DROP TABLE IF EXISTS `transaction_voucher`;
CREATE TABLE `transaction_voucher` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '收款凭证主键ID',
  `channel_id` bigint(20) unsigned NOT NULL COMMENT '渠道ID',
  `channel_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '关联channel_account.id',
  `bank_account_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '关联bank_account.id',
  `collection_card_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收款卡编号',
  `collection_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '收款金额',
  `collection_fee` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '收款手续费',
  `collection_time` datetime DEFAULT NULL COMMENT '收款时间',
  `collection_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1等待核销 2处理中 3已经核销 4核销失败 5撤销)',
  `collection_source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '转账凭证来源:0未定义1人工创建2平台内部接口3平台开放下游接口4上游回调接口',
  `transaction_voucher` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '转账的凭证UTR/platform_order_no/金额/上游订单号',
  `transaction_voucher_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '转账凭证类型：1utr 2订单id 3平台订单号 4金额 5上游订单号',
  `order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '匹配的订单编号',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原始内容',
  `operation_admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作管理员',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `transaction_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '交易类型：1代收 2代付',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_channel_id` (`channel_id`) USING BTREE,
  KEY `idx_channel_account` (`channel_account_id`) USING BTREE,
  KEY `idx_bank_account` (`bank_account_id`) USING BTREE,
  KEY `idx_collection_card_no` (`collection_card_no`) USING BTREE,
  KEY `idx_collection_status` (`collection_status`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE,
  KEY `transaction_voucher` (`transaction_voucher`,`transaction_voucher_type`,`transaction_type`)
) ENGINE=InnoDB AUTO_INCREMENT=47114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交易凭证管理表';

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID,主键',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `user_type` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '100' COMMENT '用户类型:100=系统用户',
  `nickname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户头像',
  `signed` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '个人签名',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:1=正常,2=停用',
  `login_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1' COMMENT '最后登陆IP',
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后登陆时间',
  `backend_setting` json DEFAULT NULL COMMENT '后台设置数据',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建者',
  `updated_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新者',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `is_enabled_google` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'google验证(0停用 1正常)',
  `google_secret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Google验证密钥',
  `is_bind_google` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已绑定Google验证(0否 1是)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户信息表';

-- ----------------------------
-- Table structure for user_belongs_role
-- ----------------------------
DROP TABLE IF EXISTS `user_belongs_role`;
CREATE TABLE `user_belongs_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `role_id` bigint(20) NOT NULL COMMENT '角色id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_role` (`user_id`,`role_id`),
  KEY `idx_user_role` (`user_id`,`role_id`),
  KEY `idx_role_user` (`role_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for user_dept
-- ----------------------------
DROP TABLE IF EXISTS `user_dept`;
CREATE TABLE `user_dept` (
  `user_id` bigint(20) NOT NULL,
  `dept_id` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户-部门关联表';

-- ----------------------------
-- Table structure for user_login_log
-- ----------------------------
DROP TABLE IF EXISTS `user_login_log`;
CREATE TABLE `user_login_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '登录IP地址',
  `os` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '操作系统',
  `browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '浏览器',
  `status` smallint(6) NOT NULL DEFAULT '1' COMMENT '登录状态 (1成功 2失败)',
  `message` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '提示消息',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `user_login_log_username_index` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=449 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='登录日志表';

-- ----------------------------
-- Table structure for user_operation_log
-- ----------------------------
DROP TABLE IF EXISTS `user_operation_log`;
CREATE TABLE `user_operation_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `method` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求方式',
  `router` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求路由',
  `service_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '业务名称',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '请求IP地址',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `request_params` longtext COLLATE utf8mb4_unicode_ci COMMENT '请求参数',
  `response_status` int(11) DEFAULT NULL COMMENT '响应状态码',
  `is_success` tinyint(1) DEFAULT '1' COMMENT '操作是否成功(1:成功,0:失败)',
  `response_data` longtext COLLATE utf8mb4_unicode_ci COMMENT '响应数据',
  `operator_id` bigint(20) unsigned DEFAULT '0' COMMENT '操作者ID',
  `request_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'uuid',
  `request_duration` bigint(20) unsigned DEFAULT NULL COMMENT '请求耗时(毫秒)',
  PRIMARY KEY (`id`),
  KEY `user_operation_log_username_index` (`username`),
  KEY `user_operation_log_created_at_index` (`created_at`),
  KEY `user_operation_log_service_name_index` (`service_name`),
  KEY `user_operation_log_operator_id_index` (`operator_id`),
  KEY `user_operation_log_operator_id_created_at_index` (`operator_id`,`created_at`),
  KEY `user_operation_log_created_at_service_name_index` (`created_at`,`service_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1350 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- ----------------------------
-- Table structure for user_position
-- ----------------------------
DROP TABLE IF EXISTS `user_position`;
CREATE TABLE `user_position` (
  `user_id` bigint(20) NOT NULL,
  `position_id` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户-岗位关联表';

SET FOREIGN_KEY_CHECKS = 1;
