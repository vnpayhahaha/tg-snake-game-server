<?php

namespace app\service\bankbill\down;


use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

class DtoBillOfAxisNeft implements ModelExcel
{
    #[ExcelProperty(value: 'Beneficiary Name', index: 0)]
    public string $beneficiary_name;
    #[ExcelProperty(value: 'Beneficiary Account number', index: 1)]
    public string $beneficiary_account_number;
    #[ExcelProperty(value: 'IFSC code', index: 2)]
    public string $ifsc_code;
    #[ExcelProperty(value: 'Amount', index: 3)]
    public string $amount;
    #[ExcelProperty(value: 'Description / Purpose', index: 4)]
    public string $description;

    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $item) {
            $result[] = [
                'beneficiary_name'           => $item['bank_account']['account_holder'] ?? '',
                'beneficiary_account_number' => $item['bank_account']['account_number'] ?? '',
                'ifsc_code'                  => $item['payee_bank_code'] ?? '',
                'amount'                     => (string)$item['amount'],
                'description'                => $item['platform_order_no'] ?? '',
            ];
        }
        return $result;
    }
}