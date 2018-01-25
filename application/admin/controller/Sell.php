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
class Sell extends Controller
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

	    if ($this->request->param('sell_type')) {
	        $map['a.sell_type'] = ["like", "%" . $this->request->param('sell_type') . "%"];
	    }

	    if ($this->request->param('start_date')) {
	        $map['FROM_UNIXTIME(a.order_time, "%Y-%m-%d")'] = [">=",  $this->request->param('start_date')];
	    }
	    if ($this->request->param('end_date')) {
	        $map['FROM_UNIXTIME(a.order_time, "%Y-%m-%d") '] = ["<=",  $this->request->param('end_date')];
	    }

	    $map['_field'] = 'a.id,a.compact_no,a.sell_type,a.order_time,s.client_name,a.status,a.deliver_time';
	    $map['_func'] = function ($model) {
	    	$model->alias('a')->join('client s', 'a.client=s.id', 'left');
	    };

	}

	public function beforeIndex(){
		$this->fieldIsDelete = 'a.isdelete';
	}

	public function beforerecycleBin(){
		$this->fieldIsDelete = 'a.isdelete';
	}
	/**
	 * 添加个人采购清单
	 */
	public function add(){
		$model = model('sell', 'logic');

		if ($this->request->isAjax()) {
			$post = $this->request->post();
			$validate = validate('sell');
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
					$data['goods_info'][] = $insert;
				}
			}
			if (empty($data['goods_info'])) {
				return ajax_return_adv_error('请填写产品信息！');
			}
			//采购商品表数据组装
			$data['base_info']['compact_no'] = $post['order_no']; 
			$data['base_info']['create_time'] = time();
			$data['base_info']['client'] = $post['client_id']; 
			$data['base_info']['order_time'] = !empty($post['date']) ? strtotime($post['date']) : time();
			$data['base_info']['deliver_time'] = !empty($post['deliver_time']) ? strtotime($post['deliver_time']) : time();
			$data['base_info']['user_id'] = UID;
			$data['base_info']['bussiness_uid'] = UID;
			$data['base_info']['remark'] = $post['remarks'];
			// $data['base_info']['bills_status'] = $post['status'];
			$data['base_info']['sell_type'] = $post['sell_type'];
			$data['base_info']['total_money'] = $post['total_money'];
			$data['base_info']['total_num'] = $post['total_num'];

			$res = $model->insertSell($data);
			if ($res === true) {
    			return ajax_return_adv();
    		}else{
    			return ajax_return_adv_error($res);
    		}
		}
		$goods_model = model('goods');
		$this->view->assign('goods', $goods_model->getList());
		$cient_model = model('client', 'logic');
		$this->view->assign('clients', $cient_model->getList());
		$this->view->assign('order_no', $model->createOrderNo());
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
					Db::name('sell_goods')->where('id', $data['ids'][$key])->update($insert);
				}
			}
			return true;
		}
		$this->view->assign('ischeck', \org\Auth::AccessCheck('check', UID));
		$cient_model = model('client', 'logic');
		$this->view->assign('clients', $cient_model->getList());
		$goods_model = model('goods');
		$this->view->assign('goods', $goods_model->getList());
	}

	public function ajaxDeleteGoods(){
		$id = $this->request->param('id');
		if ($id < 0) {
			return ajax_return_adv_error("缺少参数ID");
		}
		if (false === Db::name('sell_goods')->delete($id)) {
			return ajax_return_adv_error('删除失败！');
		}
		return ajax_return_adv("删除成功");
	}

	/**
	 * 采购商品列表
	 * @return HTML
	 */
	public function store(){
		$id = $this->request->param('id');
		$lists = DB::name('sell_goods')
					->alias('a')
					->field('a.*')
					->field('a.*,g.name,g.model,u.name as uname')
					->join('goods g', 'a.goods_no=g.goods_no', 'left')
					->join('goods_unit u', 'g.unit=u.id', 'left')
					->where('a.pid', $id)
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


	public function delete(){
		$id = $this->request->param('id');
		if (intval($id) <= 0) {
            return ajax_return_adv_error("缺少参数ID");
        }
		$model = model('sell', 'logic');
		$res = $model->deleteSell($id);
		if ($res === true) {
			return ajax_return_adv();
		}else{
			return ajax_return_adv_error($res);
		}

	}

	public function excel()
    {
    	$map = [];
    	$this->filter_down($map);
        $header = ['订单编号', '产品代码', '产品名称', '规格型号', '单位', '数量', '含税单价', '价税合计', '业务员', '订单日期', '订单状态', '审核状态', '备注'];
        $data = DB::name('sell')
        			->alias('a')
        			->field('a.compact_no,b.goods_no,g.name,g.model,c.name as unit,b.num,b.tax_price,b.total_price,u.username,from_unixtime(a.order_time, "%Y-%m-%d") as order_date,case a.isdelete when 0 then "正常" when 1 then "作废" end as dtxt,case a.status when 0 then "待审核" when 1 then "已审核" end as stxt,b.remark')
        			->join('sell_goods b', 'a.id=b.pid', 'left')
        			->join('user u', 'a.bussiness_uid=u.id', 'left')
        			->join('goods g', 'b.goods_no=g.goods_no', 'left')
        			->join('goods_unit c', 'g.unit=c.id', 'left')
        			->where($map)
        			->order('a.order_time desc')
        			->select();
        \Excel::export($header, $data, "销售合同列表", '2007');
    }




}