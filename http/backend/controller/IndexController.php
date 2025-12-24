<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\NoNeedLogin;
use app\Router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\Router\Annotations\RestController;
use app\service\SystemConfigService;
use app\service\upload\UploadFile;
use DI\Attribute\Inject;
use support\Request;


#[RestController("/admin")]
final class IndexController extends BasicController
{
    #[Inject]
    protected SystemConfigService $service;

    #[GetMapping('/home')]
    #[NoNeedLogin]
    public function index(Request $request)
    {
//        return $this->success([
//            'dd' => trans('hello')
//        ]);
//        $validator = validate($request->post(), [
//            'title' => 'required|unique:posts|max:255',
//            'body'  => 'required',
//        ]);
//        if ($validator->fails()) {
//            return $this->error($validator->errors()->first());
//        }
//        $params = $request->all();
//        $result1 = sys_config($params['name']);
//
//        return $this->success([
//            'result1' => $result1,
//        ]);
        $params = $request->all();
        UploadFile::getDefaultConfig();
        return $this->success();
    }

    #[PostMapping('/update')]
    #[NoNeedLogin]
    public function updateConfig(Request $request)
    {
        $data = $request->post();
        $this->service->updateData($data);
        return $this->success();
    }

}
