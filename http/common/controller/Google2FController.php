<?php

namespace http\common\controller;

use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use DI\Attribute\Inject;
use PragmaRX\Google2FA\Google2FA;

#[RestController("/v1/common/google2f")]
class Google2FController extends BasicController
{
    #[Inject]
    protected Google2FA $google2FA;

    // 生成谷歌密钥
    #[GetMapping('/generate')]
    public function generate(): \support\Response
    {
        $generateSecretKey = $this->google2FA->generateSecretKey();
        return $this->success([
            'secret' => $generateSecretKey,
        ]);
    }
}
