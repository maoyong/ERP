<?php
namespace app\admin\validate;

use think\Validate;

class Supplier extends Validate
{
    protected $rule = [
        'client_no|供应商代码'       => 'require',
        'client_name|供应商名称'     => 'require',
        'address|联系地址'      => 'require',
        'tel|联系电话'      =>'require|number',
        'ident_no|纳税人识别号' => 'require',
        'bank_name|开户行'       => 'require',
        'bank_no|开户账户'     => 'require',
        'bank_address|开户地址'     => 'require',
        'bank_tel|开户手机号'     =>'require|number'
    ];
    protected $scene = [
        'edit'  => [
            'client_no',
            'client_name',
            'tel',
            'address',
            'bank_name'
        ],
    ];

}
