<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Purchase extends Model
{
	public $field = true;

	protected $type = [
        'status'    =>  'integer',
        'order_time'  =>  'timestamp:Y-m-d',
        'check_time'  =>  'timestamp:Y-m-d',
    ];


	public function find($id){
 		$info = $this::get($id);
 		if (empty($info)) {
 			return [];
 		}
 		$info['user_info'] = DB::name('user')->field('username,realname')->where('id', $info['user_id'])->find();
 		$info['goods'] = DB::name('Purchase_goods')->where('pid', $id)->select();
 		return $info;
 	}
}
