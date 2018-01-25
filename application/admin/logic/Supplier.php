<?php

namespace app\admin\logic;

use think\Config;
use think\exception\HttpResponseException;
use think\Db;
use think\Url;

class Supplier
{

	public function insertClient($data){
		Db::startTrans();
        try {
            $id = Db::name("Supplier")->insertGetId($data['base_info']);
            if ($id < 0) {
            	throw new \Exception('操作失败！');
            }
            $data['bank_info']['band_id'] = (int)$id;
            if (isset($data['link_info'])) {
            	foreach ($data['link_info'] as $key => &$value) {
            		$value['link_id'] = (int)$id;
            	}
            }
            Db::name("bank")->insert($data['bank_info']);
            Db::name("link")->insertAll($data['link_info']);
            
            // 提交事务
            Db::commit();

            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return $e->getMessage();
        }
	}

    public function getList(){
        return DB::name('supplier')->field('id, client_name')->where(['isdelete' => 0])->select();
    }

}