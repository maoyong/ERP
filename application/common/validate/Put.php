<?php
namespace app\common\validate;

use think\Validate;

class Put extends Validate
{
    protected $rule = [
        'compact_no|单据编号'   => 'require'
    ];
}
