<?php

namespace app\middleware;

use support\Context;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class LangMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $acceptLanguage = $request->header('accept-language', 'zh_cn');
        $locale = parseAcceptLanguage($acceptLanguage);
        locale($locale);
        Context::set('locale', $locale);
        var_dump('=LangMiddleware=locale==', $locale);
        return $handler($request);
    }

    // zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6
//    private function parseAcceptLanguage(string $acceptLanguage): string
//    {
//        // 将字符串拆分为单个语言标签
//        $languages = explode(',', $acceptLanguage);
//
//        // 解析每个语言标签
//        foreach ($languages as $language) {
//            $parts = explode(';', $language);
//            $code = trim($parts[0]);
//
//            // 将连字符替换为下划线
//            $code = str_replace('-', '_', $code);
//
//            // 检查是否为有效的语言代码
//            if (preg_match('/^[a-z]{2}_[A-Z]{2}$/', $code) || preg_match('/^[a-z]{2}$/', $code)) {
//                return $code;
//            }
//        }
//
//        // 如果以上都不匹配，则回退到默认语言
//        return 'zh_CN';
//    }
}
