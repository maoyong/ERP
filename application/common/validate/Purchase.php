<?php
namespace app\common\validate;

use think\Validate;

class Purchase extends Validate
{
    protected $rule = [
        'order_no|订单编号'   => 'require',
        'compact_no|合同编号'   => 'require'
    ];
}
