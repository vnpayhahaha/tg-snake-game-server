<?php

namespace app\service\bankbill\down;

use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

#[ExcelData]
class DtoBillOfYesMsMe implements ModelExcel
{
    #[ExcelProperty(value: "Sr No", index: 0)]
    public string $sr_no;

    #[ExcelProperty(value: "Name", index: 1)]
    public string $name;

    #[ExcelProperty(value: "Transfer Type", index: 2)]
    public string $transfer_type;

    #[ExcelProperty(value: "Acc No", index: 3)]
    public string $acc_no;

    #[ExcelProperty(value: "Amount", index: 4)]
    public string $amount;

    #[ExcelProperty(value: "IFSC", index: 5)]
    public string $ifsc;

    #[ExcelProperty(value: "Phone No", index: 6)]
    public string $phone_no;

    #[ExcelProperty(value: "Remarks", index: 7)]
    public string $remarks;

    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $key => $item) {
            $sr_no = $key + 1;
            $result[] = [
                'sr_no'         => $sr_no,
                'name'          => $item['bank_account']['account_holder'] ?? '',
                'transfer_type' => 'IMPS',
                'acc_no'        => isset($item['bank_account']['account_number']) && filled($item['bank_account']['account_number']) ?
                    $item['bank_account']['account_number'] : 0,
                'amount'        => (float)number_format((float)$item['amount'], 2, '.', ''),
                'ifsc'          => $item['payee_bank_code'] ?? '',
                'phone_no'      => (int)generateIndianMobileNum(),
                'remarks'       => ($item['payee_account_no'] ?? '') . '-' . $item['platform_order_no'],
            ];
        }
        return $result;
    }
}