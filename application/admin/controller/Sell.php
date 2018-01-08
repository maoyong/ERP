<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Validate;

/**
 * 销售管理
 */
class Sell extends Main{

	public function sellList(){
		return $this->fetch();
	}

	public function addSell(){
		return $this->fetch();
	}

}