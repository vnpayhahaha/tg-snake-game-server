<?php

namespace app\service\bankbill\down;

use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

#[ExcelData]
class DtoBillOfIdfc implements ModelExcel
{
    #[ExcelProperty(value: "Beneficiary Name", index: 0)]
    public string $beneficiary_name;

    #[ExcelProperty(value: "Beneficiary Account Number", index: 1)]
    public string $beneficiary_account_no;

    #[ExcelProperty(value: "IFSC", index: 2)]
    public string $ifsc;

    #[ExcelProperty(value: "Transaction Type", index: 3)]
    public string $transaction_type;

    #[ExcelProperty(value: "Debit Account Number", index: 4)]
    public string $debit_account_no;

    #[ExcelProperty(value: "Transaction Date", index: 5)]
    public string $transaction_date;

    #[ExcelProperty(value: "Amount", index: 6)]
    public string $amount;

    #[ExcelProperty(value: "Currency", index: 7)]
    public string $currency;

    #[ExcelProperty(value: "Beneficiary Email ID", index: 8)]
    public string $beneficiary_email_id;

    #[ExcelProperty(value: "Remarks", index: 9)]
    public string $remarks;

    #[ExcelProperty(value: "Custom Header – 1", index: 10)]
    public string $custom_header_1;

    #[ExcelProperty(value: "Custom Header – 2", index: 11)]
    public string $custom_header_2;

    #[ExcelProperty(value: "Custom Header – 3", index: 12)]
    public string $custom_header_3;

    #[ExcelProperty(value: "Custom Header – 4", index: 13)]
    public string $custom_header_4;

    #[ExcelProperty(value: "Custom Header – 5", index: 14)]
    public string $custom_header_5;

    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $key => $item) {
            $result[] = [
                'beneficiary_name'       => $item['bank_account']['account_holder'] ?? '',
                'beneficiary_account_no' => $item['bank_account']['account_number'] ?? '',
                'ifsc'                   => $item['payee_bank_code'] ?? '',
                'transaction_type'       => 'NEFT',
                'debit_account_no'       => $item['payee_account_no'] ?? '',
                'transaction_date'       => date('d/m/Y'),
                'amount'                 => number_format((float)$item['amount'], 2, '.', ''),
                'currency'               => 'INR',
                'beneficiary_email_id'   => '',
                'remarks'                => $item['platform_order_no'] ?? '',
            ];
        }
        return $result;
    }
}