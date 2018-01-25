<?php
namespace app\admin\logic;

use think\Config;
use think\exception\HttpResponseException;
use think\Db;
use think\Url;

class Put 
{

    public function createOrderNo(){
        $id = Db::name('put')->where("FROM_UNIXTIME(create_time, '%Y-%m')", date('Y-m', time()))->count('id');
        return 'RKD' . date('Ym') . sprintf('%06d', ++$id);
    }

	public function insertPut($data){
		Db::startTrans();
        try {
            $id = Db::name("put")->insertGetId($data['base_info']);
            if ($id < 0) {
            	throw new \Exception('操作失败！');
            }
            if (isset($data['goods_info'])) {
            	foreach ($data['goods_info'] as $key => &$value) {
            		$value['pid'] = (int)$id;
            	}
            }
            Db::name("store_goods")->insertAll($data['goods_info']);
            Db::name('purchase')->update(['is_put' => 1, 'id' => $data['purchase_id']]);
            
            // 提交事务
            Db::commit();

            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return $e->getMessage();
        }
	}

     public function deletePut($id){
        Db::startTrans();
        try {
            $info = Db::name('put')->field('order_no,status')->where('id', $id)->find();
            if ($info['status'] == 1) {
                throw new \Exception('审核通过了，不能删除！');
            }
            if ($info['order_no'] != '') {
                Db::name('purchase')->where(['order_no' => $info['order_no']])->update(['is_put' => 0]);
            }
            Db::name('put')->update(['isdelete' => 1, 'id' => $id]);
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