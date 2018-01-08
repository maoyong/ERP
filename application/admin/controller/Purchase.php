<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Validate;

/**
 * 采购管理
 */
class Purchase extends Main
{	
	/**
	 * 个人采购清单
	 * @return HTML 
	 */
	public function index(){
		$lists = DB::name('purchase')
					->field('a.*,b.username')
					->alias('a')
					->join('user b', 'a.user_id=b.id')
					->where(['user_id' => $this->user_id])
					->order('id desc')
					->paginate(self::PAGESIZE);
		$this->assign('lists', $lists);
		return $this->fetch();
	}

	/**
	 * 添加个人采购清单
	 */
	public function add(){
		if ($this->request->isAjax()) {
			$post = $this->request->post();

			$validate = validate('Purchase');
			$model = model('purchase');
			$data = ['order_sn' => $model->createOrderNo()];
			$data['create_time'] = time();
			$data['sell_name'] = $post['sell_name'];
			$data['user_id'] = $this->user_id;
			$data['remark'] = $post['remarks'];
			$data['rate'] = $post['rate'];
			$data['rate_money'] = $post['rate_money'];
			$data['pay_money'] = $post['pay_money'];
			$data['total_money'] = $post['total_money'];
			$data['total_num'] = $post['total_num'];
			$data['buy_time'] = strtotime($post['date']);
			if (!$validate->check($data)) {
				return $this->error($validate->getError());
			}
			//采购主表数据组装
			$id = DB::name('purchase')->insertGetId($data);
			if ($id < 0) {
				$this->error('操作失败！');
			}
			$inserts = [];
			foreach ($post['name'] as $key => $val) {
				if ($val != '') {
					$insert = [];
					$insert['name'] = $val;
					$insert['unit'] = $post['unit'][$key];
					$insert['type'] = $post['type'][$key];
					$insert['num'] = $post['num'][$key];
					$insert['price'] = $post['price'][$key];
					$insert['remark'] = $post['remark'][$key];
					$insert['pid'] = $id;
					$inserts[] = $insert;
				}
			}
			//采购商品表数据组装
			$post['pid'] = $id;
			$res = DB::name('purchase_goods')->insertAll($inserts);
			if ($res) {
				$this->success('操作成功！');
			}else{
				$this->error('操作失败！');
			}

			return false;
		}
		
		return $this->fetch();
	}

	/**
	 * 采购商品列表
	 * @return HTML
	 */
	public function goodsList(){
		$id = $this->request->get('id');
		$lists = DB::name('purchase_goods')
					->where('pid', $id)
					->paginate(self::PAGESIZE);
		$this->assign('lists', $lists);
		return $this->fetch();
	}


	public function purchaseTotal(){
		return $this->fetch();
	}

}