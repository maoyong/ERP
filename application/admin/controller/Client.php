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

	class Client extends Controller
	{
		use \traits\controller\Jump;
	    use \app\admin\traits\controller\Controller;
	    // 视图类实例
	    protected $view;
	    // Request实例
	    protected $request;
	    
	     protected function filter(&$map)
	    {
	        //不查询管理员
	        //$map['id'] = ["gt", 1];

	        if ($this->request->param('name')) {
	            $map['name'] = ["like", "%" . $this->request->param('name') . "%"];
	        }
	    }

	}

