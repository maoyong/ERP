<?php
namespace app\admin\logic;

use think\Config;
use think\exception\HttpResponseException;
use think\Db;
use think\Url;

class Sell 
{
	public function createOrderNo(){
		$id = mt_rand(1, 100000000);
		return 'XSD' . date('Ymd') . sprintf('%08d', $id);
	}


	public function insertSell($data){
		Db::startTrans();
        try {
            $id = Db::name("sell")->insertGetId($data['base_info']);
            if ($id < 0) {
            	throw new Exception('操作失败！');
            }
            if (isset($data['goods_info'])) {
            	foreach ($data['goods_info'] as $key => &$value) {
            		$value['pid'] = (int)$id;
            	}
            }
            Db::name("sell_goods")->insertAll($data['goods_info']);
            
            // 提交事务
            Db::commit();

            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return $e->getMessage();
        }
	}
}
