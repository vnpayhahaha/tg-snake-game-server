<?php

namespace http\openapi\controller;

use app\controller\BasicController;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use support\Request;
use support\Response;

#[RestController("/openapi")]
class IndexController extends BasicController
{
    #[PostMapping('/home')]
    public function index(Request $request): Response
    {
        return $this->success([
            'dd' => trans('hello')
        ]);
    }
}
