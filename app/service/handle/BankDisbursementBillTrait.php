<?php

namespace app\service\handle;

use app\repository\BankDisbursementUploadRepository;
use app\repository\DisbursementOrderVerificationQueueRepository;
use DI\Attribute\Inject;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDateHelper;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

trait BankDisbursementBillTrait
{
    private array $fieldMap;
    #[Inject]
    protected BankDisbursementUploadRepository $uploadRepository;
    #[Inject]
    protected DisbursementOrderVerificationQueueRepository $verificationQueueRepository;
    //eg: protected array $FieldMap = [
    //        'pymt_mode'            => 'pymt_mode',
    //        'file_sequence_num'    => 'file_sequence_num',
    //        'debit_acct_no'        => 'debit_acct_no',
    //        'beneficiaryname'      => 'beneficiary_name',
    //        'beneficiaryaccountno' => 'beneficiary_account_no',
    //        'bene_ifsc_code'       => 'bene_ifsc_code',
    //        'amount'               => 'amount',
    //        'remark'               => 'remark',
    //        'pymt_date'            => 'pymt_date',
    //        'status'               => 'status',
    //        'customerrefno'        => 'customer_ref_no',
    //        'utrno'                => 'utr_no',
    //    ];
    protected array $FieldMap;

    // 解析数据
    public function parseData(int $upload_id, string $file_path, ?\Closure $closure = null): array
    {
        // excel 读取数据
        $spreadsheet = IOFactory::load('./public' . $file_path);
//        var_dump('==$file_path==', $file_path, $spreadsheet);

        //工作表
        $workSheet = $spreadsheet->getSheet(0);
        //总行数
        $workRow = $workSheet->getHighestDataRow();
        //总列数
        $workColumn = Coordinate::columnIndexFromString($workSheet->getHighestColumn());
        //设置表头数据
        $fieldMap = $this->SetFiledMap($workSheet, $workColumn);
        //var_dump('$workColumn', $workColumn);
        if (empty($fieldMap)) {
            throw new \RuntimeException('The table header field was not obtained');
        }

        $record_count = $this->getRowCount($workSheet);
        dump('==$record_count==', $record_count);

        $this->uploadRepository->getModel()->where('id', $upload_id)->update(['record_count' => $record_count - 1]);
        // var_dump('==$fieldMap=====', $workRow, $fieldMap);
        //获取数据
        for ($i = 2; $i <= $workRow; $i++) {
            $data = [];
            for ($j = 1; $j <= $workColumn; $j++) {
                if (!isset($fieldMap[$j])) {
                    continue;
                }
                $key = $fieldMap[$j];
                //$columnKey = columnIndexToString($j) . $i;
                //var_dump("---{$i}-{$j}-{$columnKey}--{$key}--value-");
                //$value = $workSheet->getCell(columnIndexToString($j) . $i)->getValue();
                $value = $this->getCellValue($workSheet->getCell(columnIndexToString($j) . $i));
                //var_dump("--{$columnKey}--{$key}--value-", $value);
                if (!filled($value)) {
                    continue;
                }
                $data[$key] = preg_replace('/\s/', '', (string)$value);
            }
            //var_dump("--{$i}--", $data);
            if ($closure) {
                $bill_data = $closure($data);
                if ($bill_data && isset($bill_data['order_no'], $bill_data['amount'])) {
                    // dump("--{$i}--", $bill_data);
                    // 插入支付订单核销队列
                    $this->verificationQueueRepository->create([
                        'platform_order_no' => $bill_data['order_no'],
                        'amount'            => $bill_data['amount'],
                        'utr'               => $bill_data['utr'],
                        'payment_status'    => $bill_data['payment_status'],
                        'rejection_reason'  => $bill_data['rejection_reason'],
                        'order_data'        => json_encode($data, JSON_THROW_ON_ERROR),
                        'next_retry_time'   => date('Y-m-d H:i:s', time() + 60 * 3),
                    ]);
                }
            }

        }

        return [];
    }

    // 获取总行数（过滤空行）
    public function getRowCount($workSheet): int
    {
        // 获取总行数和总列数
        $workRow = $workSheet->getHighestDataRow();
        $highestColumn = $workSheet->getHighestDataColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        // 过滤空行
        $nonEmptyRows = [];
        for ($row = 1; $row <= $workRow; $row++) {
            $isEmpty = true;
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cell = $workSheet->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . 1);
                $cellValue = $cell->getValue();
                // $cellValue = $workSheet->getCellByColumnAndRow($col, $row)->getValue();
                if ($cellValue !== null && $cellValue !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if (!$isEmpty) {
                $nonEmptyRows[] = $row; // 记录非空行
            }
        }

        // 非空行数
        return count($nonEmptyRows);
    }

    protected function getCellValue($cell, $date_format = "Y-m-d H:i:s")
    {
        $value = $cell->getValue();
        //logger()->info('=getCellValue=$cell==', [$value]);
        if ($cell->getDataType() === DataType::TYPE_NUMERIC) {
            //var_dump('==$value==',$value);
            //版本过低的话请加上 getParent 例：$cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat();
            $cell_style_format = $cell->getStyle($cell->getCoordinate())->getNumberFormat(); //不需要getParent
            $format_code = $cell_style_format->getFormatCode();
            if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $format_code)) { //判断是否为日期类型
                $value = gmdate($date_format, (int)SharedDateHelper::excelToTimestamp($value)); //格式化日期
            } else {
                $value = NumberFormat::toFormattedString($value, $format_code); //格式化数字
            }
        } // 解析日期时间如果是 Sat Jun 14 00:00:00 IST 2025
        if (filled($value)) {
            if ($dateTime = \DateTime::createFromFormat('D M d H:i:s T Y', $value)) {
                $value = $dateTime->format($date_format);
            }
        }

        return $value;
    }

    protected function SetFiledMap($workSheet, $workColumn): array
    {
        for ($i = 1; $i <= $workColumn; $i++) {
            $cell = $workSheet->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i) . 1);
            $title = $cell->getValue();
            if (!filled($title)) {
                continue;
            }
            // 过滤$title所有空格和换行
            $title = preg_replace('/\s/', '', $title);
            // $title转小写
            $title = strtolower($title);
            //var_dump('====$i===', $i,'====$title===', $title);
            if (isset($this->FieldMap[$title])) {
                $this->fieldMap[$i] = $this->FieldMap[$title];
            }
        }
        return $this->fieldMap ?? [];
    }
}