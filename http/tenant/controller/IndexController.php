<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use support\Request;

#[RestController("/tenant")]
class IndexController extends BasicController
{
    #[GetMapping('/home')]
    public function index(Request $request)
    {
        return $this->success([
            'dd' => trans('hello')
        ]);
    }
}
