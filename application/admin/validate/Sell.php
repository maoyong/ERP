<?php
namespace app\admin\validate;

use think\Validate;

class Sell extends Validate
{
    protected $rule = [
        'order_no|订单编号'   => 'require',
        'sell_type|销售方式'   => 'require'
    ];
}
