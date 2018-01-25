<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Goods extends Model
{
	public $field = true;
    protected $type = [
        'status'    =>  'integer',
        'create_time'  =>  'timestamp:Y-m-d',
        'update_time'  =>  'timestamp:Y-m-d',
    ];

 	public function getList(){
 		$goods = DB::name('goods')
					->alias('a')
					->field('a.name, a.goods_no, a.unit,b.name as uname,a.model')
					->join('goods_unit b', 'a.unit=b.id', 'left')
					->where('a.isdelete', 0)
					->order('a.sort desc,a.id desc')
					->select();
		return $goods;
 	}

 	public function getDataByName($name){
 		$where = ['a.isdelete' => 0];
 		$where['a.name'] = ['like','%'.$name.'%'];
 		$goods = DB::name('goods')
					->alias('a')
					->field('a.name, a.goods_no, a.unit,b.name as uname,a.model')
					->join('goods_unit b', 'a.unit=b.id', 'left')
					->where($where)
					->select();
		return $goods;
 	}

}
