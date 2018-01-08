<?php
namespace app\index\controller;

use \think\Controller;

class Index extends Controller
{
    public function index()
    {
      return $this->fetch();
    }

    public function testGet(){
    	$get = $this->request->get();
    	return json($get);
    }
    public function testPost(){
    	$post = $this->request->post();
    	return json($post);
    }
}
