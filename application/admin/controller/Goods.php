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

	class Goods extends Controller
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

	    public function beforeIndex(){
	    	$lists = DB::name('goods_unit')->select();
	    	$units = [];
	    	foreach ($lists as $key => $value) {
	    		$units[$value['id']] = $value['name']; 
	    	}
	    	$this->view->assign('units', $units);
	    }

	    public function beforeAdd(){
	    	$this->view->assign('create_time', time());
	    	$this->view->assign('units', DB::name('goods_unit')->select());
	    }

	    public function beforeEdit(){
	    	$this->view->assign('units', DB::name('goods_unit')->select());
	    }

	     protected function filter(&$map)
	    {
	        //不查询管理员
	        //$map['id'] = ["gt", 1];

	        if ($this->request->param('name')) {
	            $map['name'] = ["like", "%" . $this->request->param('name') . "%"];
	        }
	    }

	}

