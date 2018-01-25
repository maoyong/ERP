<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Sell extends Model
{
	public $field = true;

	protected $type = [
        'status'    =>  'integer',
        'order_time'  =>  'timestamp:Y-m-d',
        'check_time'  =>  'timestamp:Y-m-d',
        'deliver_time' => 'timestamp:Y-m-d',
        'create_time'  =>  'timestamp:Y-m-d',
        'update_time'  =>  'timestamp:Y-m-d',
    ];


	public function find($id){
 		$info = $this::get($id);
 		if (empty($info)) {
 			return [];
 		}
 		$info['user_info'] = DB::name('user')->field('username,realname')->where('id', $info['user_id'])->find();
        $info['goods'] = DB::name('sell_goods')
                            ->alias('a')
                            ->field('a.*,g.name,g.model,u.name as uname')
                            ->join('goods g', 'a.goods_no=g.goods_no', 'left')
                            ->join('goods_unit u', 'g.unit=u.id', 'left')
                            ->where('pid', $id)
                            ->select();
        $info['clients'] = DB::name('client')->field('client_name')->where('id', $info['client'])->find();
 		return $info;
 	}
}
