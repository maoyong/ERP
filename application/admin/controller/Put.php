<?php

namespace app\admin\controller;
\think\Loader::import('controller/Controller', \think\Config::get('traits_path'), EXT);
\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);
use app\admin\Controller;
use think\Loader;
use think\Session;
use think\Db;
use think\Config;
use think\Exception;
use think\View;
use think\Request;

/**
 * 采购管理
 */
class Put extends Controller
{	

	use \traits\controller\Jump;
    use \app\admin\traits\controller\Controller;
    // 视图类实例
    protected $view;
    // Request实例
    protected $request;
	/**
	 * 个人采购清单
	 * @return HTML 
	 */
	protected function filter(&$map)
	{
	    if ($this->request->param('name')) {
	        $map['a.compact_no'] = ["like", "%" . $this->request->param('name') . "%"];
	    }

	    if ($this->request->param('supplier')) {
	        $map['s.client_name'] = ["like", "%" . $this->request->param('supplier') . "%"];
	        unset($map['supplier']);
	    }

	    if ($this->request->param('start_date')) {
	        $map['FROM_UNIXTIME(a.put_time, "%Y-%m-%d")'] = [">=",  $this->request->param('start_date')];
	    }
	    if ($this->request->param('end_date')) {
	        $map['FROM_UNIXTIME(a.put_time, "%Y-%m-%d") '] = ["<=",  $this->request->param('end_date')];
	    }

	    $map['_field'] = 'a.order_no,a.id,a.compact_no,a.put_type,a.position,a.put_time,s.client_name,a.status';
	    $map['_func'] = function ($model) {
	    	$model->alias('a')->join('supplier s', 'a.supplier=s.id', 'left');
	    };

	}

	public function beforeIndex(){
		$this->fieldIsDelete = 'a.isdelete';
	}

	public function beforerecycleBin(){
		$this->fieldIsDelete = 'a.isdelete';
	}


	/**
	 * ajax获取采购订单的商品详情
	 * @return [type] [description]
	 */
	public function ajaxGetInfo(){
		if ($this->request->isAjax()) {
			$id = $this->request->param('id');
			$model = model('purchase');
			$info = $model->find($id);
			return ajax_return($info, 'success');
		}
		return ajax_return_adv_error();
	}

	public function ajaxDeleteGoods(){
		$id = $this->request->param('id');
		if ($id < 0) {
			return ajax_return_adv_error("缺少参数ID");
		}
		if (false === Db::name('store_goods')->delete($id)) {
			return ajax_return_adv_error('删除失败！');
		}
		return ajax_return_adv("删除成功");
	}

	/**
	 * 添加个人采购清单
	 */
	public function add(){
		$model = model('put', 'logic');

		if ($this->request->isAjax()) {
			$post = $this->request->post();
			$validate = validate('put');
			if (!$validate->check($post)) {
				return ajax_return_adv_error($validate->getError());
			}
			$data = [];
			//组装产品数据
			foreach ($post['num'] as $key => $val) {
				if ($val != '') {
					$insert = [];
					$insert['goods_no'] = $post['no'][$key];
					$insert['num'] = $post['num'][$key];
					$insert['unit_price'] = $post['unit_price'][$key];
					$insert['tax_price'] = $post['tax_price'][$key];
					$insert['price'] = $post['price'][$key];
					$insert['rate'] = $post['rates'][$key];
					$insert['rate_price'] = $post['rate_prices'][$key];
					$insert['total_price'] = $post['total_price'][$key];
					$insert['remark'] = $post['remark'][$key];
					$insert['cate'] = 0; //入库类型
					$data['goods_info'][] = $insert;
				}
			}
			if (empty($data['goods_info'])) {
				return ajax_return_adv_error('请填写产品信息！');
			}
			//采购商品表数据组装
			$data['base_info']['compact_no'] = $post['compact_no']; 
			$data['base_info']['order_no'] = $post['order_no']; 
			$data['base_info']['create_time'] = time();
			$data['base_info']['put_time'] = !empty($post['date']) ? strtotime($post['date']) : time();
			$data['base_info']['user_id'] = UID; //创建人
			$data['base_info']['bussiness_uid'] = UID; //业务员
			$data['base_info']['remark'] = $post['remarks'];
			$data['base_info']['supplier'] = $post['supplier_id'];
			$data['base_info']['position'] = $post['position']; //仓库
			$data['base_info']['put_type'] = $post['put_type']; //入库类别
			$data['base_info']['total_money'] = $post['total_money'];
			$data['base_info']['total_num'] = $post['total_num'];
			//更新采购状态
			$data['purchase_id'] = $post['purchase_id'];
			$res = $model->insertPut($data);
			if ($res === true) {
    			return ajax_return_adv();
    		}else{
    			return ajax_return_adv_error($res);
    		}
		}

		$goods_model = model('goods');
		$this->view->assign('goods', $goods_model->getList());
		$this->view->assign('order_no', $model->createOrderNo());
		$supplier_model = model('Supplier', 'logic');
		$this->view->assign('suppliers', $supplier_model->getList());
		$this->view->assign('purchases', DB::name('purchase')->field('id,order_no')->where(['status'=>1, 'is_put' => 0, 'isdelete' => 0])->select());
		return $this->view->fetch();
	}

	public function beforeEdit(){
		if ($this->request->isAjax()) {
			$data = $this->request->post();
            if (!$data['id']) {
                return ajax_return_adv_error("缺少参数ID");
            }
            foreach ($data['num'] as $key => $val) {
				if ($val != '') {
					$insert = [];
					$insert['goods_no'] = $data['no'][$key];
					$insert['num'] = $data['num'][$key];
					$insert['unit_price'] = $data['unit_price'][$key];
					$insert['tax_price'] = $data['tax_price'][$key];
					$insert['price'] = $data['price'][$key];
					$insert['rate'] = $data['rates'][$key];
					$insert['rate_price'] = $data['rate_prices'][$key];
					$insert['total_price'] = $data['total_price'][$key];
					$insert['remark'] = $data['remarks'][$key];
					Db::name('store_goods')->where('id', $data['ids'][$key])->update($insert);
				}
			}

			if ($data['status'] == 1) {
				$store = model('store', 'logic');
				$store->updateStore($data['id'], 0, 0);//添加
			}else{
				$store = model('store', 'logic');
				$store->updateStore($data['id'], 0, 1);
			}
			return true;
		}

		$this->view->assign('ischeck', \org\Auth::AccessCheck('check', UID));
		$goods_model = model('goods');
		$this->view->assign('goods', $goods_model->getList());
		$supplier_model = model('Supplier', 'logic');
		$this->view->assign('suppliers', $supplier_model->getList());
	}

	public function Delete(){
		$id = $this->request->param('id');
		if (intval($id) <= 0) {
            return ajax_return_adv_error("缺少参数ID");
        }
		$model = model('put', 'logic');
		$res = $model->deletePut($id);
		if ($res === true) {
			return ajax_return_adv();
		}else{
			return ajax_return_adv_error($res);
		}

	}

	/**
	 * 采购商品列表
	 * @return HTML
	 */
	public function store(){
		$id = $this->request->param('id');
		$lists = DB::name('store_goods')
					->alias('a')
					->field('a.*')
					->field('a.*,g.name,g.model,u.name as uname')
					->join('goods g', 'a.goods_no=g.goods_no', 'left')
					->join('goods_unit u', 'g.unit=u.id', 'left')
					->where(['a.pid' => $id, 'cate' => 0])
					->select();
		$this->view->assign('lists', $lists);
		return $this->view->fetch();
	}



	/**
	 * 下载的查询过滤
	 * @param  Array &$map 查询数组
	 * @return        
	 */
	protected function filter_down(&$map){
		if ($this->request->param('name')) {
	        $map['a.order_no'] = ["like", "%" . $this->request->param('name') . "%"];
	    }

	    if ($this->request->param('sell_type')) {
	        $map['a.sell_type'] = ["like", "%" . $this->request->param('sell_type') . "%"];
	    }

	    if ($this->request->param('month')) {
	        $map['FROM_UNIXTIME(a.order_time, "%Y-%m")'] = ["=",  $this->request->param('month')];
	    }

	    return true;
	}


	public function excel()
    {
    	$map = ['cate' => 0];
    	$this->filter_down($map);
        $header = ['单据编号', '产品代码', '产品名称', '规格型号', '单位', '数量', '采购日期', '入库日期', '入库状态', '单据状态', '备注'];
        $data = DB::name('put')
        			->alias('a')
        			->field('a.compact_no,b.goods_no,g.name,g.model,c.name as unit,b.num,from_unixtime(p.order_time, "%Y-%m-%d") as order_date,from_unixtime(a.put_time, "%Y-%m-%d") as put_time,case a.isdelete when 0 then "正常" when 1 then "作废" end as dtxt,case a.status when 0 then "待审核" when 1 then "已审核" end as stxt,b.remark')
        			->join('store_goods b', 'a.id=b.pid', 'left')
        			->join('user u', 'a.bussiness_uid=u.id', 'left')
        			->join('goods g', 'b.goods_no=g.goods_no', 'left')
        			->join('goods_unit c', 'g.unit=c.id', 'left')
        			->join('purchase p', 'a.order_no=p.order_no', 'left')
        			->where($map)
        			->order('a.put_time desc')
        			->select();
        \Excel::export($header, $data, "入库列表", '2007');
    }

    public function printf(){
    	$id = $this->request->param('id');
    	$model = model('Put');
    	$info = $model->find($id, 1);
    	$this->view->assign('info', $info);
    	return $this->view->fetch();
    }




}