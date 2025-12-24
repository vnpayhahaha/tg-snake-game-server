<?php

namespace app\service\bankbill\down;

use app\lib\annotation\ExcelData;
use app\lib\LdlExcel\ModelExcel;
use app\lib\annotation\ExcelProperty;

#[ExcelData]
class DtoBillOfICICI2 implements ModelExcel
{
    //Transaction type (Within Bank (WIB)  NEFT (NFT) RTGS (RTG) IMPS (IFC))
    #[ExcelProperty(value: "Transaction type", index: 0)]
    public string $transaction_type;

    //	Amount (₹) (Should not be more than 15 digit including decimals and paise)
    #[ExcelProperty(value: "Amount (₹)", index: 1)]
    public string $amount;

    //	Debit Account no Should be exactly 12 digit
    #[ExcelProperty(value: "Debit Account no", index: 2)]
    public string $debit_acc_no;

    //	IFSC (Always 11 character alphanumeric and 5th character always 0 (zero)) (For ICICI bank accounts keep it blank)
    #[ExcelProperty(value: "IFSC", index: 3)]
    public string $bene_ifsc;

    //Beneficiary Account No (Max length for other bank 34 character alphanumeric and for ICICI Bank 12 digit number )
    #[ExcelProperty(value: "Beneficiary Account No", index: 4)]
    public string $bene_acc_no;

    //Beneficiary Name (Max length 32 Character) (No Special Character is allowed but Space is allowed)
    #[ExcelProperty(value: "Beneficiary Name", index: 5)]
    public string $bene_name;

    //	Remarks for Client (should not be more than 21 characters)
    #[ExcelProperty(value: "Remarks for Client", index: 6)]
    public string $remark_for_client;

    //	Remarks for Beneficiary (should not be more than 30 characters)
    #[ExcelProperty(value: "Remarks for Beneficiary", index: 7)]
    public string $remark_for_beneficiary;


    public static function formatData($orderData): array
    {
        $result = array();
        foreach ($orderData as $item) {
            $result[] = [
                'transaction_type'       => 'IMPS',
                'amount'                 => (string)$item['amount'],
                'debit_acc_no'           => $item['payee_account_no'] ?? '',
                'bene_ifsc'              => $item['payee_bank_code'] ?? '',
                'bene_acc_no'            => $item['bank_account']['account_number'] ?? '',
                'bene_name'              => $item['bank_account']['account_holder']  ?? '',
                'remark_for_client'      => $item['platform_order_no'],
                'remark_for_beneficiary' => $item['platform_order_no'],
            ];
        }
        return $result;
    }
}