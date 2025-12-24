<?php

namespace app\lib\factory;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as LaravelValidationFactory;

class ValidatorFactory
{
    public static $instance = [];

    public static function getInstance(string $translationLocale = 'zh_cn'): Factory
    {
        if (!isset(static::$instance[$translationLocale])) {
            $translationPath = config('translation.path');
            $transFileLoader = new FileLoader(new Filesystem(), $translationPath);
            $translator = new Translator($transFileLoader, $translationLocale);
            static::$instance[$translationLocale] = new LaravelValidationFactory($translator, Container::getInstance());
        }
        return static::$instance[$translationLocale];
    }
}
