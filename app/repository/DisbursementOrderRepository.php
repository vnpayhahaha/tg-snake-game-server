<?php

namespace app\repository;

use app\constants\DisbursementOrder;
use app\model\ModelDisbursementOrder;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DisbursementOrderRepository.
 * @extends IRepository<ModelDisbursementOrder>
 */
final class DisbursementOrderRepository extends IRepository
{
    #[Inject]
    protected ModelDisbursementOrder $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['allocation']) && filled($params['allocation'])) {
            if ($params['allocation'][0] == 1) {
                $query->where('disbursement_channel_id', 0);
            } elseif ($params['allocation'][0] == 2) {
                $query->where('disbursement_channel_id', '>', 0);
            }
        }
        if (isset($params['platform_order_no']) && filled($params['platform_order_no'])) {
            $query->where('platform_order_no', $params['platform_order_no']);
        }

        if (isset($params['tenant_order_no']) && filled($params['tenant_order_no'])) {
            $query->where('tenant_order_no', $params['tenant_order_no']);
        }

        if (isset($params['upstream_order_no']) && filled($params['upstream_order_no'])) {
            $query->where('upstream_order_no', $params['upstream_order_no']);
        }

        if (isset($params['pay_time']) && filled($params['pay_time'])) {
            $query->where('pay_time', $params['pay_time']);
        }

        if (isset($params['order_source']) && filled($params['order_source'])) {
            $query->where('order_source', $params['order_source']);
        }

        if (isset($params['disbursement_channel_id']) && filled($params['disbursement_channel_id'])) {
            $query->where('disbursement_channel_id', $params['disbursement_channel_id']);
        }

        if (isset($params['bank_account_id']) && filled($params['bank_account_id'])) {
            $query->where('bank_account_id', $params['bank_account_id']);
        }
        if (isset($params['channel_account_id']) && filled($params['channel_account_id'])) {
            $query->where('channel_account_id', $params['channel_account_id']);
        }

        if (isset($params['payment_type']) && filled($params['payment_type'])) {
            $query->where('payment_type', $params['payment_type']);
        }

        if (isset($params['payee_bank_name']) && filled($params['payee_bank_name'])) {
            $query->where('payee_bank_name', $params['payee_bank_name']);
        }

        if (isset($params['payee_bank_code']) && filled($params['payee_bank_code'])) {
            $query->where('payee_bank_code', $params['payee_bank_code']);
        }

        if (isset($params['payee_account_name']) && filled($params['payee_account_name'])) {
            $query->where('payee_account_name', $params['payee_account_name']);
        }

        if (isset($params['payee_account_no']) && filled($params['payee_account_no'])) {
            $query->where('payee_account_no', $params['payee_account_no']);
        }

        if (isset($params['payee_upi']) && filled($params['payee_upi'])) {
            $query->where('payee_upi', $params['payee_upi']);
        }

        if (isset($params['utr']) && filled($params['utr'])) {
            $query->where('utr', $params['utr']);
        }

        if (isset($params['platform_transaction_no']) && filled($params['platform_transaction_no'])) {
            $query->where('platform_transaction_no', $params['platform_transaction_no']);
        }

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['app_id']) && filled($params['app_id'])) {
            $query->where('app_id', $params['app_id']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            if ($params['status'] == 40) {
                $query->where('status', '>=', $params['status']);
            } else {
                $query->where('status', $params['status']);
            }
        }

        if (isset($params['expire_time']) && filled($params['expire_time'])) {
            $query->where('expire_time', $params['expire_time']);
        }

        if (isset($params['notify_status']) && filled($params['notify_status'])) {
            $query->where('notify_status', $params['notify_status']);
        }

        if (isset($params['channel_transaction_no']) && filled($params['channel_transaction_no'])) {
            $query->where('channel_transaction_no', $params['channel_transaction_no']);
        }

        if (isset($params['request_id']) && filled($params['request_id'])) {
            $query->where('request_id', $params['request_id']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('channel:id,channel_name,channel_code,channel_icon')
            ->with('channel_account:id,merchant_id')
            ->with('bank_account:id,branch_name')
            ->with('cancel_operator:id,username,nickname')
            ->with('bank_disbursement_download:id,file_name,suffix,hash')
            ->with('cancel_customer:id,username,avatar')
            ->with('created_customer:id,username,avatar')
            ->with('transaction_record:id,transaction_no,transaction_status')
            ->with('status_records')
            ->with('settlement_status:id,transaction_no,transaction_status,transaction_type,settlement_delay_mode,settlement_delay_days,expected_settlement_time,failed_msg,remark')
            ->paginate(
                perPage: $pageSize,
                pageName: static::PER_PAGE_PARAM_NAME,
                page: $page,
            );
        $pageData = $this->handlePage($result);
        // 统计数据
        $order_amount = $this->perQuery($this->getQuery(), $params)->sum('amount');
        return [
            ...$pageData,
            'order_amount'   => $order_amount,
        ];
    }

    // 构建订单凭证图片 交易凭证
    public function buildOrderPaymentImage(ModelDisbursementOrder $paymentOrder): string
    {
        $file_path = public_path() . '/transaction/payment_order';
        // $file_name = $paymentOrder->platform_order_no . '_' . 状态 . '.png';
        $file_name = $paymentOrder->platform_order_no . '_' . $paymentOrder->status . '.png';
        $save_path = $file_path . '/' . $file_name;

        // 如果文件已经存在，则直接返回文件路径
        if (file_exists($save_path)) {
            return $file_name;
        }

        if ($paymentOrder->status <= DisbursementOrder::STATUS_WAIT_FILL) {
            $Status = 'Paying';
        } else if ($paymentOrder->status === DisbursementOrder::STATUS_SUCCESS) {
            $Status = 'Paid successfully';
        } else if ($paymentOrder->status === DisbursementOrder::STATUS_SUSPEND) {
            // 处理中
            $Status = 'In processing';
        } else {
            $Status = 'Payment failed';
        }
        $order = [
            'Transaction ID'   => $paymentOrder->platform_order_no,
            'UTR'              => $paymentOrder->utr,
            'Transaction Date' => date('d/m/Y H:i:s', strtotime($paymentOrder->pay_time)),
            'Bank IFSC'        => $paymentOrder->payee_bank_code,
            'Account Number'   => $paymentOrder->payee_account_no,
            'Name Of Payee'    => $paymentOrder->payee_account_name,
            'Amount'           => $paymentOrder->amount,
            'Status'           => $Status,
        ];

        // 将$order转换成 name => value的二维数组
        $data = array_map(function ($key, $value) {
            return [
                'name'  => $key,
                'value' => $value,
            ];
        }, array_keys($order), array_values($order));

        $params = [
            'row'       => count($data),//数据的行数
            'file_name' => $file_name,
            'title'     => 'Payment Receipt',
            'data'      => $data
        ];
        $base = [
            'border'                 => 54,//图片外边框
            'file_path'              => 'pic/',//图片保存路径
            'title_height'           => 30,//报表名称高度
            'title_font_size'        => 16,//报表名称字体大小
            'font_ulr'               => 'resource/fonts/Deng.ttf',//字体文件路径
            'text_size'              => 12,//正文字体大小
            'row_hight'              => 40,//每行数据行高
            'filed_id_width'         => 60,//序号列的宽度
            'filed_name_width'       => 300,//玩家名称的宽度
            'filed_data_width'       => 600,//数据列的宽度
            'table_header'           => ['', 'Below is the Detail of transaction:'],//表头文字
            'table_bottom'           => ['', 'Please keep this receipt for a valid proof of the transaction'],//表头文字
            'column_text_offset_arr' => [200, 200, 55, 55, 55, 65, 65],//表头文字左偏移量
            'row_text_offset_arr'    => [0, 280, 580, 90, 90, 90, 90],//数据列文字左偏移量
        ];

        $base['img_width'] = $base['filed_name_width'] + $base['filed_data_width'] + $base['border'] * 2;//图片宽度
        $base['img_height'] = $params['row'] * $base['row_hight'] + $base['border'] * 2 + $base['title_height'];//图片高度
        $border_top = $base['border'] + $base['title_height'];//表格顶部高度
        $border_bottom = $base['img_height'] - $base['border'];//表格底部高度
        $base['column_x_arr'] = [
            $base['border'],//第一列边框线x轴像素
            $base['border'] + $base['filed_name_width'],//第二列边框线x轴像素
            $base['border'] + $base['filed_name_width'] + $base['filed_data_width'] * 1,//第三列边框线x轴像素
            //            $base['border'] + $base['filed_id_width'] + $base['filed_name_width'] + $base['filed_data_width'] * 2,//第四列边框线x轴像素
            //            $base['border'] + $base['filed_id_width'] + $base['filed_name_width'] + $base['filed_data_width'] * 3,//第五列边框线x轴像素
            //            $base['border'] + $base['filed_id_width'] + $base['filed_name_width'] + $base['filed_data_width'] * 4,//第五列边框线x轴像素
            //            $base['border'] + $base['filed_id_width'] + $base['filed_name_width'] + $base['filed_data_width'] * 5,//第五列边框线x轴像素
        ];
        $img = imagecreatetruecolor($base['img_width'], $base['img_height']);//创建指定尺寸图片
        $bg_color = imagecolorallocate($img, 255, 250, 250);//设定图片背景色
        $text_coler = imagecolorallocate($img, 0, 0, 0);//设定文字颜色
        $border_coler = imagecolorallocate($img, 0, 0, 0);//设定边框颜色
        $white_coler = imagecolorallocate($img, 255, 255, 255);//设定边框颜色
        imagefill($img, 0, 0, $bg_color);//填充图片背景色
        //先填充一个黑色的大块背景
        imagefilledrectangle($img, $base['border'], $base['border'] + $base['title_height'], $base['img_width'] - $base['border'], $base['img_height'] - $base['border'], $border_coler);//画矩形
        //再填充一个小两个像素的 背景色区域，形成一个两个像素的外边框
        imagefilledrectangle($img, $base['border'] + 2, $base['border'] + $base['title_height'] + 2, $base['img_width'] - $base['border'] - 2, $base['img_height'] - $base['border'] - 2, $bg_color);//画矩形
        //画表格纵线 及 写入表头文字
        foreach ($base['column_x_arr'] as $key => $x) {
            imageline($img, $x, $border_top, $x, $border_bottom, $border_coler);//画纵线
            //imagettftext($img, $base['text_size'], 0, $x - $base['column_text_offset_arr'][$key] + 1, $border_top + $base['row_hight'] - 8, $text_coler, $base['font_ulr'], $base['table_header'][$key]);//写入表头文字
        }

        //画表格横线
        foreach ($params['data'] as $key => $item) {
            $border_top += $base['row_hight'];
            imageline($img, $base['border'], $border_top, $base['img_width'] - $base['border'], $border_top, $border_coler);

            //imagettftext($img, $base['text_size'], 0, $base['column_x_arr'][0] - $base['row_text_offset_arr'][0], $border_top + $base['row_hight'] - 10, $text_coler, $base['font_ulr'], $key + 1);//写入序号
            $sub = 0;
            foreach ($item as $value) {
                $sub++;
                imagettftext($img, $base['text_size'], 0, $base['column_x_arr'][$sub] - $base['row_text_offset_arr'][$sub], $border_top - 10, $text_coler, $base['font_ulr'], $value);//写入data数据
            }
        }

        //计算标题写入起始位置
        $title_fout_box = imagettfbbox($base['title_font_size'], 0, $base['font_ulr'], $params['title']);//imagettfbbox() 返回一个含有 8 个单元的数组表示了文本外框的四个角：
        $title_fout_width = $title_fout_box[2] - $title_fout_box[0];//右下角 X 位置 - 左下角 X 位置 为文字宽度
        $title_fout_height = $title_fout_box[1] - $title_fout_box[7];//左下角 Y 位置- 左上角 Y 位置 为文字高度
        //居中写入标题
        imagettftext($img, $base['title_font_size'], 0, ($base['img_width'] - $title_fout_width) / 2, $base['title_height'], $text_coler, $base['font_ulr'], $params['title']);
        //写入制表时间 table_header table_bottom
        imagettftext($img, $base['text_size'], 0, $base['border'], $base['title_height'] + $base['row_hight'] - 10, $text_coler, $base['font_ulr'], $base['table_header'][1]);
        imagettftext($img, $base['text_size'], 0, $base['border'], $base['title_height'] + $border_top, $text_coler, $base['font_ulr'], $base['table_bottom'][1]);

        // 修改目录创建方式
        if (!is_dir($file_path)) {
            mkdir($file_path, 0755, true);
        }

        imagepng($img, $save_path);//输出图片，输出png使用imagepng方法，输出gif使用imagegif方法
        imagedestroy($img);
        // base_path等于 $filePath 去掉前面 public_path()
        return str_replace(public_path(), '', $save_path);
    }

    public function queryCountOrderNum(string $queryWhereSql, string $startTime, string $endTime = null): int
    {
        if ($endTime === null) {
            $endTime = date('Y-m-d H:i:s');
        }
        return $this->getQuery()
            ->whereRaw("1 {$queryWhereSql}")
            ->whereBetween('created_at', [$startTime, $endTime])
            ->count();
    }

    public function queryOrderSuccessfulNum(string $queryWhereSql, string $startTime, string $endTime = null): int
    {
        if ($endTime === null) {
            $endTime = date('Y-m-d H:i:s');
        }
        return $this->getQuery()
            ->whereRaw("1 {$queryWhereSql}")
            ->whereBetween('created_at', [$startTime, $endTime])
            ->where('status', DisbursementOrder::STATUS_SUCCESS)
            ->count();
    }
}
