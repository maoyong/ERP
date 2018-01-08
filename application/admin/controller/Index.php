<?php
namespace app\admin\controller;

namespace app\admin\controller;

use app\admin\Controller;
use think\Loader;
use think\Session;
use think\Db;
class Index extends Controller
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function child()
    {
        return $this->view->fetch();
    }

    public function main()
    {
        return $this->view->fetch();
    }

    public function welcome()
    {
        return $this->view->fetch();
    }

}
