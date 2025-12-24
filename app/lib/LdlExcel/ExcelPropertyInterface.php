<?php

namespace app\lib\LdlExcel;

use app\model\BasicModel;
use Psr\Http\Message\ResponseInterface;

interface ExcelPropertyInterface
{
    public function import(BasicModel $model, ?\Closure $closure = null): mixed;

    public function export(string $filename, string $suffix, string $down_filepath, array|\Closure $closure): \support\Response;
}