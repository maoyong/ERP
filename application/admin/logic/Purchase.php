<?php
namespace app\admin\logic;

use think\Config;
use think\exception\HttpResponseException;
use think\Db;
use think\Url;

class Purchase 
{
	public function createOrderNo(){
		$id = Db::name('put')->where("FROM_UNIXTIME(create_time, '%Y-%m')", date('Y-m', time()))->count('id');
        return 'CGD' . date('Ym') . sprintf('%06d', ++$id);
	}


	public function insertPurchase($data){
		Db::startTrans();
        try {
            $id = Db::name("purchase")->insertGetId($data['base_info']);
            if ($id < 0) {
            	throw new \Exception('操作失败！');
            }
            if (isset($data['goods_info'])) {
            	foreach ($data['goods_info'] as $key => &$value) {
            		$value['pid'] = (int)$id;
            	}
            }
            Db::name("purchase_goods")->insertAll($data['goods_info']);
            
            // 提交事务
            Db::commit();

            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return $e->getMessage();
        }
	}

    public function deletePurchase($id){
        Db::startTrans();
        try {
            $info = Db::name('purchase')->field('order_no,status')->where('id', $id)->find();
            if ($info['status'] == 1 && !\org\Auth::AccessCheck('check', UID)) {
                throw new \Exception('审核通过了，不能删除！');
            }
            Db::name('purchase')->update(['isdelete' => 1, 'id' => $id]);
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
