<?php

namespace app\lib\JwtAuth\handle;

class Url extends RequestToken
{
    public function handle()
    {
        return request()->get('token');
    }
}
