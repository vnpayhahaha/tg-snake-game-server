<?php

namespace app\lib\JwtAuth\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\lib\JwtAuth\exception\JwtException;
use app\lib\JwtAuth\handle\RequestToken;
use app\lib\JwtAuth\facade\JwtAuth;

class JwtAuthMiddleware implements MiddlewareInterface
{

    public function process(Request $request,callable $next): Response
    {
        if ($request->method() === 'OPTIONS') {
            response( '',204 );
        }
        if ($route = $request->route) {
            $store = $route->param( 'store' );
        }
        $store   = $store ?? ( \request()->app === '' ? 'default' : \request()->app );
        $JwtAuth = new \app\lib\JwtAuth\JwtAuth( $store );
        try {
            $requestToken = new RequestToken();
            $jwtConfig    = $JwtAuth->getConfig();
            $handel       = $jwtConfig->getType();
            $token        = $requestToken->get( $handel );
            $JwtAuth->verify( $token );
            $jwtConfig->getUserModel() && $request->user = JwtAuth::getUser();
            return $next( $request );
        } catch ( JwtException $e ) {
            throw new JwtException( $e->getMessage(),$e->getCode() );
        }
    }
}
