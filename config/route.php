<?php

use Webman\Http\Response;
use Webman\Route;

Route::options('[{path:.+}]', function ($request) {
    return new Response(204, [
        'Access-Control-Allow-Origin'      => '*',
        'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Token, accept-language',
        'Access-Control-Expose-Headers'    => 'Authorization, Token, X-Requested-With, accept-language, X-Request-Id',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
});
// 查询开放api
Route::get('/common/api/{api}', [http\common\controller\ApiController::class,'api']);
\app\router\AnnotationProvider::start();
