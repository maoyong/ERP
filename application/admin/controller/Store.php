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

	class Store extends Controller
	{
		use \traits\controller\Jump;
	    use \app\admin\traits\controller\Controller;
	    
	    // 视图类实例
	    protected $view;
	    // Request实例
	    protected $request;

	    public function __construct(){
	    	parent::__construct();
	    }

	    protected function filter(&$map)
	    {

	        if ($this->request->param('name')) {
	            $map['g.name'] = ["like", "%" . $this->request->param('name') . "%"];
	        }
	        if ($this->request->param('brand')) {
	            $map['g.brand'] = ["like", "%" . $this->request->param('brand') . "%"];
	        }
	        if ($this->request->param('model')) {
	            $map['g.model'] = ["like", "%" . $this->request->param('model') . "%"];
	        }

	        $map['_field'] = 'a.id,g.goods_no,g.name,g.brand,g.model, u.name uname, a.store_num, g.remark';
		    $map['_func'] = function ($model) {
		    	$model->alias('a')
		    			->join('goods g', 'a.goods_no=g.goods_no', 'left')
		    			->join('goods_unit u', 'g.unit=u.id', 'left');
		    };
	    }

	

	    public function filter_down(&$map){
	    	if ($this->request->param('name')) {
	            $map['g.name'] = ["like", "%" . $this->request->param('name') . "%"];
	        }
	        if ($this->request->param('brand')) {
	            $map['g.brand'] = ["like", "%" . $this->request->param('brand') . "%"];
	        }
	        if ($this->request->param('model')) {
	            $map['g.model'] = ["like", "%" . $this->request->param('model') . "%"];
	        }
	    }

	public function excel()
    {
    	$map = [];
    	$this->filter_down($map);
        $header = ['产品代码', '产品名称', '品牌', '规格型号', '单位', '数量'];
        $data = DB::name('store')
        			->alias('a')
        			->field('a.goods_no,g.name,g.brand,g.model,c.name as unit,a.store_num')
        			->join('goods g', 'a.goods_no=g.goods_no', 'left')
        			->join('goods_unit c', 'g.unit=c.id', 'left')
        			->where($map)
        			->order('g.sort desc,g.id desc')
        			->select();
        \Excel::export($header, $data, "库存列表", '2007');
    }

	}

