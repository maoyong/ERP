<?php
namespace app\admin\logic;

use think\Config;
use think\exception\HttpResponseException;
use think\Db;
use think\Url;

class Sell 
{
	public function createOrderNo(){
        $id = Db::name('put')->where("FROM_UNIXTIME(create_time, '%Y-%m')", date('Y-m', time()))->count('id');
        return 'XSD' . date('Ym') . sprintf('%06d', ++$id);
	}


	public function insertSell($data){
		Db::startTrans();
        try {
            $id = Db::name("sell")->insertGetId($data['base_info']);
            if ($id < 0) {
            	throw new \Exception('操作失败！');
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

    public function deleteSell($id){
        Db::startTrans();
        try {
            $info = Db::name('sell')->field('status')->where('id', $id)->find();
            if ($info['status'] == 1 && !\org\Auth::AccessCheck('check', UID)) {
                throw new \Exception('审核通过了，不能删除！');
            }
            Db::name('sell')->update(['isdelete' => 1, 'id' => $id]);
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
