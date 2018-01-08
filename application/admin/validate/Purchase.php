<?php
namespace app\admin\validate;

use think\Validate;

class Purchase extends Validate
{
    protected $rule = [
        'rate_money'   => 'require',
        'pay_money'    => 'require',
        'total_money'  => 'require',
    ];
    protected $message = [
        'rate_money.require'  => '折扣后金额必须',
        'pay_money.require'   => '付款金额必须',
        'total_money.require' => '合计金额必须',
    ];
}
