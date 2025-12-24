<?php

namespace app\service\bankbill\down;


use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

#[ExcelData]
class DtoBillOfAxis implements ModelExcel
{
    #[ExcelProperty(value: 'Payment Method Name', index: 0)]
    public string $payment_method_name;
    #[ExcelProperty(value: 'Payment Amount (Request)', index: 1)]
    public string $payment_amount_request;
    #[ExcelProperty(value: 'Activation Date', index: 2)]
    public string $activation_date;
    #[ExcelProperty(value: 'Beneficiary Name (Request)', index: 3)]
    public string $beneficiary_name_request;
    #[ExcelProperty(value: 'Account No', index: 4)]
    public string $account_no;
    #[ExcelProperty(value: 'Email', index: 5)]
    public string $email;
    #[ExcelProperty(value: 'Email Body', index: 6)]
    public string $email_body;
    #[ExcelProperty(value: 'Debit Account No', index: 7)]
    public string $debit_account_no;
    #[ExcelProperty(value: 'CRN No', index: 8)]
    public string $crn_no;
    #[ExcelProperty(value: 'RECEIVER IFSC Code', index: 9)]
    public string $receiver_ifsc_code;
    #[ExcelProperty(value: 'RECEIVER Account Type', index: 10)]
    public string $receiver_account_type;
    #[ExcelProperty(value: 'Remarks', index: 11)]
    public string $remarks;
    #[ExcelProperty(value: 'Phone No', index: 12)]
    public string $phone_no;

    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $item) {
            $result[] = [
                'payment_method_name'      => 'N',
                'payment_amount_request'   => (string)$item['amount'],
                'activation_date'          => date('Y/m/d H:i:s', strtotime($item['created_at'])),
                'beneficiary_name_request' => $item['bank_account']['account_holder'] ?? '',
                'account_no'               => $item['bank_account']['account_number'] ?? '',
                'email'                    => '',
                'email_body'               => '',
                'debit_account_no'         => $item['payee_account_no'] ?? '',
                'crn_no'                   => $item['platform_order_no'] ?? '',
                'receiver_ifsc_code'       => $item['payee_bank_code'] ?? '',
                'receiver_account_type'    => '10',
                'remarks'                  => $item['platform_order_no'] ?? '',
                'phone_no'                 => generateIndianMobileNum(),
            ];
        }
        return $result;
    }
}