<?php
namespace app\admin\logic;

use think\Config;
use think\exception\HttpResponseException;
use think\Db;
use think\Url;

class Store 
{


    /**
     * 出入库的数量操作
     * @param  Int  $id     出入库表的id
     * @param  int  $type   类型，出库0入库1
     * @param  integer $status 添加，还是减少
     * @return boolean          
     */
	public function updateStore($id, $type, $status=0){
		Db::startTrans();
        try {       
            switch ($type) {
                case 0: //入库
                    $lists = Db::name("store_goods")
                            ->alias('a')
                            ->join('put b', 'a.pid=b.id', 'left')
                            ->field('a.goods_no,a.num')
                            ->where(['b.id' => $id, 'a.cate' => $type, 'b.status' => $status])
                            ->select();
                    foreach ($lists as $key => $list) {
                        if ($status == 0) {//是要启用入库
                            DB::name('store')->where(['goods_no' => $list['goods_no']])->setInc('store_num', $list['num']);
                        }else{ //要禁用出库
                            DB::name('store')->where(['goods_no' => $list['goods_no']])->setDec('store_num', $list['num']);
                        }
                    }
                    break;
                case 1: //出库
                    $lists = Db::name("store_goods")
                            ->alias('a')
                            ->join('delivery b', 'a.pid=b.id', 'left')
                            ->field('a.goods_no,a.num')
                            ->where(['b.id' => $id, 'a.cate' => $type, 'b.status' => $status])
                            ->select();
                    foreach ($lists as $key => $list) {
                        if ($status == 0) {//是要启用入库
                            DB::name('store')->where(['goods_no' => $list['goods_no']])->setDec('store_num', $list['num']);
                        }else{ //要禁用出库
                            DB::name('store')->where(['goods_no' => $list['goods_no']])->setInc('store_num', $list['num']);
                        }
                    }
                    break; 
                default:
                    return false;
                    break;
            }
            
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
