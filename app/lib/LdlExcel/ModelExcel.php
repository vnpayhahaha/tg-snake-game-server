<?php

namespace app\lib\LdlExcel;

use Illuminate\Support\Collection;

interface ModelExcel
{
    public static function formatData(Collection $orderData): array;
}