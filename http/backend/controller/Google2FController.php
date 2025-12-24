<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use chillerlan\QRCode\QRCode;
use DI\Attribute\Inject;
use PragmaRX\Google2FA\Google2FA;
use support\Request;
use support\Response;

#[RestController("/admin/google2f")]
class Google2FController extends BasicController
{

    #[Inject]
    protected Google2FA $google2FA;

    // 获取谷歌QR码URL，以便用户扫描 getGoogleQRCodeUrl
    #[GetMapping('/getQRCode/{secretKey}')]
    public function getQRCodeUrl(Request $request, string $secretKey): Response
    {
        $companyName = env('APP_NAME', 'LangDaLang');
        $username = $request->user->username;
        $googleQRCodeUrl = $this->google2FA->getQRCodeUrl(
            $companyName.' Management',
            $username,
            $secretKey
        );
        $qrCodeString = (new QRCode())->render($googleQRCodeUrl);
        return $this->success([
            'qr_code' => $qrCodeString,
        ]);
    }

    // verify
    #[GetMapping('/verify/{code}')]
    public function verify(Request $request, string $code): Response
    {
        $user = $request->user;
        $userSecretKey = $user->google_secret ?? '';
        var_dump('verify===',$userSecretKey);
        $googleSecretKey = $request->input('secret_key', $userSecretKey);
        var_dump('$googleSecretKey===',filled($googleSecretKey) ?: $userSecretKey);
        $is_pass = $this->google2FA->verifyKey(filled($googleSecretKey) ?: $userSecretKey, $code);
        return $this->success([
            'is_pass' => $is_pass,
        ]);
    }

}