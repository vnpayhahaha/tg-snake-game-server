<?php

namespace app\service\bankbill\down;

use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

#[ExcelData]
class DtoBillOfAxisNeo implements ModelExcel
{
    #[ExcelProperty(value: 'Debit Account Number', index: 0)]
    public string $debit_account_number;
    #[ExcelProperty(value: 'Transaction Amount', index: 1)]
    public string $transaction_amount;
    #[ExcelProperty(value: 'Transaction Currency', index: 2)]
    public string $transaction_currency;
    #[ExcelProperty(value: 'Beneficiary Name', index: 3)]
    public string $beneficiary_name;
    #[ExcelProperty(value: 'Beneficiary Account Number', index: 4)]
    public string $beneficiary_account_number;
    #[ExcelProperty(value: 'Beneficiary IFSC Code', index: 5)]
    public string $beneficiary_ifsc_code;
    #[ExcelProperty(value: 'Transaction Date', index: 6)]
    public string $transaction_date;
    #[ExcelProperty(value: 'Payment Mode', index: 7)]
    public string $payment_mode;
    #[ExcelProperty(value: 'Customer Reference Number', index: 8)]
    public string $customer_reference_number;
    #[ExcelProperty(value: 'Beneficiary Nickname/Code', index: 9)]
    public string $beneficiary_nickname_code;


    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $item) {
            $result[] = [
                'debit_account_number'       => $item['payee_account_no'] ?? '',
                'transaction_amount'         => number_format((float)$item['amount'], 2, '.', ''),
                'transaction_currency'       => 'INR',
                'beneficiary_name'           => $item['bank_account']['account_holder'] ?? '',
                'beneficiary_account_number' => $item['bank_account']['account_number'] ?? '',
                'beneficiary_ifsc_code'      => $item['payee_bank_code'] ?? '',
                'transaction_date'           => date('d/m/Y H:i:s', strtotime($item['created_at'])),
                'payment_mode'               => 'IMPS',
                'customer_reference_number'  => $item['platform_order_no'],
                'beneficiary_nickname_code'  => $item['platform_order_no'],
            ];
        }
        return $result;
    }
}