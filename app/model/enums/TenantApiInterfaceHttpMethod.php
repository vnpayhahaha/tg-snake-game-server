<?php

namespace app\model\enums;

use app\constants\TenantApiInterface;

enum TenantApiInterfaceHttpMethod: string
{
    case GET = TenantApiInterface::HTTP_METHOD_GET;
    case POST = TenantApiInterface::HTTP_METHOD_POST;
    case PUT = TenantApiInterface::HTTP_METHOD_PUT;
    case DELETE = TenantApiInterface::HTTP_METHOD_DELETE;
}
