<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Delivery extends Model
{
	public $field = true;

	protected $type = [
        'status'    =>  'integer',
        'put_time'  =>  'timestamp:Y-m-d',
        'check_time'  =>  'timestamp:Y-m-d',
        'create_time'  =>  'timestamp:Y-m-d',
        'update_time'  =>  'timestamp:Y-m-d',
    ];

    protected $readonly = ['create_time'];


    /**
     * 获取入库单信息
     * @param  Int  $id       单号
     * @param  integer $is_table 是否打印表格
     * @return Array            
     */
	public function find($id, $is_table=0){
 		$info = $this::get($id);
 		if (empty($info)) {
 			return [];
 		}
        // $info['check_time'] = $info['check_time'] == '1970-01-01' ? date('Y-m-d', time()) : $info['check_time'];
 		//获取制单人信息
 		$info['user_info'] = DB::name('user')->field('username,realname')->where('id', $info['user_id'])->find();
 		//获取经手人信息
 		if ($info['put_type'] == 0 && $is_table) {
 			$info['handler'] = Db::name('sell')
 				->alias('a')
 				->field('b.username,b.realname')
 				->join('user b', 'a.user_id=b.id', 'left')
 				->where(['a.compact_no' => $info['order_no']])
 				->find();
 		}
 		//获取产品信息
 		$info['goods'] = DB::name('store_goods')
 							->alias('a')
 							->field('a.*,g.name,g.model,u.name as uname,g.brand')
 							->join('goods g', 'a.goods_no=g.goods_no', 'left')
 							->join('goods_unit u', 'g.unit=u.id', 'left')
 							->where(['pid' => $id, 'cate' => 1])
 							->select();
 		//获取供应商信息
 		if($is_table){
 			$info['suppliers'] = DB::name('client')
 									->alias('a')
 									->join('link b', 'a.id=b.link_id', 'left')
 									->field('a.client_name,a.address,b.link_name,b.link_tel')
 									->where(['a.id' => $info['supplier'], 'b.link_type' => 0])
 									->order('b.id asc')
 									->find();
 		}else{
 			$info['suppliers'] = DB::name('client')->field('client_name')->where('id', $info['supplier'])->find();
 		}
 		return $info;
 	}
}
