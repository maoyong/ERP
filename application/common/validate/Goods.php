<?php

namespace app\common\validate;

use think\Validate;

class Goods extends Validate
{
    protected $rule = [
        'goods_no|产品编码'      => 'require|unique:goods',
        'name|商品名称'      => 'require'
    ];

  
}