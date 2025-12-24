<?php

namespace app\service\bankbill\down;

use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

#[ExcelData]
class DtoBillOfIobSame implements ModelExcel
{
    #[ExcelProperty(value: 'iob or other bank', index: 0)]
    public string $iob_or_other_bank;
    #[ExcelProperty(value: 'account number', index: 1)]
    public string $account_number;
    #[ExcelProperty(value: 'amount', index: 2)]
    public string $amount;
    #[ExcelProperty(value: 'narration', index: 3)]
    public string $narration;
    #[ExcelProperty(value: 'IFSC code', index: 4)]
    public string $ifsc_code;
    #[ExcelProperty(value: 'account type', index: 5)]
    public string $account_type;
    #[ExcelProperty(value: 'name of beneficiary', index: 6)]
    public string $name_of_beneficiary;
    #[ExcelProperty(value: 'address of beneficiary', index: 7)]
    public string $address_of_beneficiary;
    #[ExcelProperty(value: 'remarks', index: 8)]
    public string $remarks;

    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $item) {
            //var_dump('===$item===', $item);
            $bank_user_name = $item['bank_account']['account_holder'] ?? '';
            // 限制 bank_user_name 长度 10 字符
            $bank_user_name = mb_substr($bank_user_name, 0, 10);
            $result[] = [
                'iob_or_other_bank'      => 'IOB',
                'account_number'         => $item['bank_account']['account_number'] ?? '',
                'amount'                 => (string)$item['amount'],
                'narration'              => $bank_user_name,
                'ifsc_code'              => $item['payee_bank_code'] ?? '',
                'account_type'           => '',
                'name_of_beneficiary'    => $bank_user_name,
                'address_of_beneficiary' => $bank_user_name,
                'remarks'                => $item['platform_order_no'] ?? '',
            ];
        }
        return $result;
    }
}