<?php

namespace app\lib\JwtAuth\user;

interface AuthorizationUserInterface
{
    public function getUserById($id);
}
