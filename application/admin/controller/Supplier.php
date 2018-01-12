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

	class Supplier extends Controller
	{
		use \traits\controller\Jump;
	    use \app\admin\traits\controller\Controller;
	    // 视图类实例
	    protected $view;
	    // Request实例
	    protected $request;
	    
	     protected function filter(&$map)
	    {
	        if ($this->request->param('name')) {
	            $map['client_name'] = ["like", "%" . $this->request->param('name') . "%"];
	        }
	    }

	    public function add(){
	    	if ($this->request->isAjax()) {
	    		$data = $this->request->except(['id']);
	    		$validate = validate('Supplier');
	    		if (!$validate->check($data)) {
	    			 return ajax_return_adv_error($validate->getError());
	    		}
	    		$data['base_info'] = [
	    			'client_no' 	=> $data['client_no'],
	    			'client_name' 	=> $data['client_name'],
	    			'address' 		=> $data['address'],
	    			'tel' 			=> $data['tel'],
	    			'ident_no' 		=> $data['ident_no'],
	    			'remark' 		=> $data['remark'],
	    			'create_time' 	=> time(),
	    			'user_id' 		=> UID
	    		];
	    		$data['bank_info'] = [
	    			'bank_name'		=> $data['bank_name'],
	    			'bank_no'		=> $data['bank_no'],
	    			'bank_address'	=> $data['bank_address'],
	    			'bank_tel'		=> $data['bank_tel'],
	    			'bank_type'		=> 1
	    		];
	    		if (isset($data['linkman'])) {
	    			foreach ($data['linkman'] as $key => $value) {
		    			$data['link_info'][] = [
		    				'link_name' => $value,
		    				'link_tel'	=> $data['linktel'][$key],
		    				'link_type' => 1
			    		];
		    		}
	    		}
	    		$client = model('supplier', 'logic');
	    		$res = $client->insertClient($data);
	    		if ($res === true) {
	    			return ajax_return_adv();
	    		}else{
	    			return ajax_return_adv_error($res);
	    		}
	    	}

	    	 return $this->view->fetch(isset($this->template) ? $this->template : 'edit');
	    }


	    /**
	     * 更新主体数据前，先更新关联数据
	     * @return  
	     */
	    public function beforeEdit(){
	    	if ($this->request->isAjax()) {
	    		$data = $this->request->post();
	            if (!$data['id']) {
	                return ajax_return_adv_error("缺少参数ID");
	            }
	            $bank_info= [
	    			'bank_name'		=> $data['bank_name'],
	    			'bank_no'		=> $data['bank_no'],
	    			'bank_address'	=> $data['bank_address'],
	    			'bank_tel'		=> $data['bank_tel'],
	    		];
	            Db::name('bank')->where('id', $data['bank_id'])->update($bank_info);
	            if (isset($data['linkman'])) {
	    			foreach ($data['linkman'] as $key => $value) {
		    			$link_info = [
		    				'link_name' => $value,
		    				'link_tel'	=> $data['linktel'][$key],
			    		];
			    		Db::name('link')->where('id', $data['linkid'][$key])->update($link_info);
		    		}
	    		}
	    	}
	    	
	    }

	}

