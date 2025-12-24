<?php

namespace app\lib\JwtAuth\handle;

class Cookie extends RequestToken
{
    public function handle()
    {
        return request()->cookie('token');
    }
}
