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
class Purchase extends Controller
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
	        $map['a.order_no'] = ["like", "%" . $this->request->param('name') . "%"];
	    }

	    if ($this->request->param('supplier')) {
	        $map['s.client_name'] = ["like", "%" . $this->request->param('supplier') . "%"];
	    }

	    if ($this->request->param('month')) {
	        $map['FROM_UNIXTIME(a.order_time, "%Y-%m")'] = ["=",  $this->request->param('month')];
	    }

	    $map['_field'] = 'a.id,a.order_no,a.compact_no,a.buy_type,a.order_time,s.client_name';
	    $map['_func'] = function ($model) {
	    	$model->alias('a')->join('supplier s', 'a.supplier=s.id');
	    };
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

	    if ($this->request->param('supplier')) {
	        $map['s.client_name'] = ["like", "%" . $this->request->param('supplier') . "%"];
	    }

	    if ($this->request->param('month')) {
	        $map['FROM_UNIXTIME(a.order_time, "%Y-%m")'] = ["=",  $this->request->param('month')];
	    }

	    return true;
	}

	/**
	 * 添加个人采购清单
	 */
	public function add(){
		$model = model('purchase', 'logic');

		if ($this->request->isAjax()) {
			$post = $this->request->post();
			$validate = validate('Purchase');
			if (!$validate->check($post)) {
				return ajax_return_adv_error($validate->getError());
			}
			$data = [];
			//组装产品数据
			foreach ($post['num'] as $key => $val) {
				if ($val != '') {
					$insert = [];
					$insert['no'] = $post['no'][$key];
					$insert['name'] = $post['name'][$key];
					$insert['unit'] = $post['unit'][$key];
					$insert['type'] = $post['type'][$key];
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
			$data['base_info']['order_no'] = $post['order_no']; 
			$data['base_info']['compact_no'] = $post['compact_no']; 
			$data['base_info']['create_time'] = time();
			$data['base_info']['order_time'] = !empty($post['date']) ? strtotime($post['date']) : time();
			$data['base_info']['supplier'] = $post['supplier_id'];
			$data['base_info']['user_id'] = UID;
			$data['base_info']['remark'] = $post['remarks'];
			$data['base_info']['bills_status'] = $post['status'];
			$data['base_info']['buy_type'] = $post['buy_type'];
			$data['base_info']['total_money'] = $post['total_money'];
			$data['base_info']['total_num'] = $post['total_num'];

			$res = $model->insertPurchase($data);
			if ($res === true) {
    			return ajax_return_adv();
    		}else{
    			return ajax_return_adv_error($res);
    		}
		}
		$suppliers = DB::name('supplier')->field('id, client_name')->select();
		$goods = DB::name('goods')->field('name, goods_no')->select();

		$this->view->assign('units', DB::name('goods_unit')->select());
		$this->view->assign('suppliers', $suppliers);
		$this->view->assign('goods', $goods);
		$this->view->assign('order_no', $model->createOrderNo());
		return $this->view->fetch();
	}

	public function beforeEdit(){
		if ($this->request->isAjax()) {
			$data = $this->request->post();
            if (!$data['id']) {
                return ajax_return_adv_error("缺少参数ID");
            }
            
            foreach ($data['name'] as $key => $val) {
				if ($val != '') {
					$insert = [];
					$insert['no'] = $data['no'][$key];
					$insert['name'] = $val;
					$insert['unit'] = $data['unit'][$key];
					$insert['type'] = $data['type'][$key];
					$insert['num'] = $data['num'][$key];
					$insert['unit_price'] = $data['unit_price'][$key];
					$insert['tax_price'] = $data['tax_price'][$key];
					$insert['price'] = $data['price'][$key];
					$insert['rate'] = $data['rates'][$key];
					$insert['rate_price'] = $data['rate_prices'][$key];
					$insert['total_price'] = $data['total_price'][$key];
					$insert['remark'] = $data['remarks'][$key];
					Db::name('purchase_goods')->where('id', $data['ids'][$key])->update($insert);
				}
			}
			return true;
		}
		$suppliers = DB::name('supplier')->field('id, client_name')->select();

		$this->view->assign('units', DB::name('goods_unit')->select());
		$this->view->assign('suppliers', $suppliers);
	}

	/**
	 * 采购商品列表
	 * @return HTML
	 */
	public function store(){
		$id = $this->request->param('id');
		$lists = DB::name('purchase_goods')
					->where('pid', $id)
					->select();
		$this->view->assign('lists', $lists);
		$lists = DB::name('goods_unit')->select();
		$units = [];
    	foreach ($lists as $key => $value) {
    		$units[$value['id']] = $value['name']; 
    	}
    	$this->view->assign('units', $units);
		return $this->view->fetch();
	}


	public function excel()
    {
    	$map = [];
    	$this->filter_down($map);
        $header = ['订单编号', '产品代码', '产品名称', '规格型号', '单位', '数量', '含税单价', '价税合计', '业务员', '订单日期', '订单状态', '备注'];
        $data = DB::name('purchase')
        			->alias('a')
        			->field('a.order_no,b.no,b.name,b.type,c.name as unit,b.num,b.price,b.total_price,u.username,from_unixtime(a.order_time, "%Y-%m-%d") as order_date,case a.status when 0 then "待审核" when 1 then "已审核" end as stxt,b.remark')
        			->join('purchase_goods b', 'a.id=b.pid', 'left')
        			->join('user u', 'a.user_id=u.id', 'left')
        			->join('goods_unit c', 'b.unit=c.id', 'left')
        			->join('supplier s', 'a.supplier=s.id')
        			->where($map)
        			->order('a.order_time desc')
        			->select();
        \Excel::export($header, $data, "采购单列表", '2007');
    }

}