<?php
namespace framework\tests\route\mock;

use framework\validate\Validate;

class UserValidator extends Validate
{
    //规则
    protected $rule = [
        'name' => 'require',
        'age' => 'require',
    ];

    //信息
    protected $message = [
        'name.require' => '参数name缺少',
        'age.require' => '参数age缺少',
    ];

    //建议方法名称对应
    protected $scene = [
        'create' => ['name', 'age'],
    ];
}
