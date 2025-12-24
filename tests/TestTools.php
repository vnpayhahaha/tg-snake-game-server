<?php

namespace tests;

use app\tools\Base62Converter;
use PHPUnit\Framework\TestCase;
class TestTools extends TestCase
{
    public function testToolsBase62Converter()
    {
        $id = 123;
        $base62 = Base62Converter::decToBase62($id,5);
        var_dump('$base62==',$base62);
        var_dump('$base62=dec=',Base62Converter::base62ToDec($base62));
        $this->assertEquals(Base62Converter::base62ToDec($base62), $id);
    }
}