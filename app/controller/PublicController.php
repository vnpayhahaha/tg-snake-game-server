<?php

namespace app\controller;

use app\lib\annotation\NoNeedLogin;
use app\lib\enum\ResultCode;
use app\router\Annotations\RequestMapping;
use app\router\Annotations\RestController;
use support\Request;
use support\Response;

#[RestController("/public")]
class PublicController extends BasicController
{

    #[RequestMapping(path: '/selectOption', methods: 'get,post')]
    #[NoNeedLogin]
    public function selectOption(Request $request): Response
    {
        $validator = validate($request->all(), [
            'table_name' => 'required|string|max:50',
            'field_list' => 'required|string|max:50', // 字段
        ]);

        if ($validator->fails()) {
            return $this->error(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $configData = config('constants', []);
        $constants = array_keys($configData);
        $fieldName = $validatedData['field_list'];
        if (in_array($validatedData['table_name'], $constants, true)) {
            $constantClass = $configData[$validatedData['table_name']] ?? null;
            if (is_null($constantClass)) {
                return $this->error(ResultCode::ENUM_NOT_FOUND);
            }
            if (!property_exists($constantClass, $fieldName)) {
                return $this->error(ResultCode::ENUM_NOT_FOUND);
            }
            try {
                $listArr = $constantClass::${$fieldName};
                $list = $constantClass::getOptionMap($listArr);
            } catch (\Throwable $e) {
                return $this->error(ResultCode::ENUM_NOT_FOUND);
            }
            return $this->success($list);
        }
        return $this->error(ResultCode::NOT_FOUND);
    }

    // 查询所有枚举状态
    #[RequestMapping(path: '/selectAllOptionField', methods: 'get,post')]
    #[NoNeedLogin]
    public function selectAllOptionField(Request $request): Response
    {
        $configData = config('constants', []);
        $constantsMap = [];
        foreach ($configData as $key => $className) {
            try {
                // 获取类的反射对象
                $reflectionClass = new \ReflectionClass($className);
                $staticProperties = $reflectionClass->getStaticProperties();

                foreach ($staticProperties as $propertyName => $propertyValue) {
                    $constantsMap[$key][] = $propertyName;
                }
            } catch (\RuntimeException $e) {
                // 处理类不存在的异常
                return $this->error(ResultCode::FAIL);
            }
        }
        return $this->success($constantsMap);
    }
}
