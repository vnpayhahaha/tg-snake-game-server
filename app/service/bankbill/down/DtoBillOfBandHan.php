<?php

namespace app\service\bankbill\down;

use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

#[ExcelData]
class DtoBillOfBandHan implements ModelExcel
{
    #[ExcelProperty(value: 'Payment Type', index: 0)]
    public string $payment_type;
    #[ExcelProperty(value: 'Beneficiary Name', index: 1)]
    public string $beneficiary_name;
    #[ExcelProperty(value: 'Bene Account number', index: 2)]
    public string $bene_account_number;
    #[ExcelProperty(value: 'Bene Bank IFSC', index: 3)]
    public string $bene_bank_ifsc;
    #[ExcelProperty(value: 'Narration', index: 4)]
    public string $narration;
    #[ExcelProperty(value: 'Amount(₹)', index: 5)]
    public string $amount;

    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $item) {
            $result[] = [
                'payment_type'        => 'NEFT',
                'beneficiary_name'    => $item['bank_account']['account_holder'] ?? '',
                'bene_account_number' => $item['bank_account']['account_number'] ?? '',
                'bene_bank_ifsc'      => $item['payee_bank_code'] ?? '',
                'narration'           => $item['platform_order_no'],
                'amount'              => (string)$item['amount'],
            ];
        }
        return $result;
    }
}