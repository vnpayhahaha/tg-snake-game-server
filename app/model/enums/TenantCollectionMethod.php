<?php

namespace app\model\enums;

use app\constants\Tenant;

enum TenantCollectionMethod: int
{

    case BankAccount = Tenant::COLLECTION_USE_METHOD_BANK_ACCOUNT;
    case Upstream = Tenant::COLLECTION_USE_METHOD_UPSTREAM;

}
