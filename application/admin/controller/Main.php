<?php
namespace app\admin\controller;

use org\Auth;
use think\Controller;
use think\Db;
use think\Session;

class Main extends Controller
{

    const PAGESIZE = 15;

    public $username;
    public $user_id;

    public function _initialize()
    {
        $this->username  = session('username');
        $this->user_id = session('user_id');

        if (empty($this->username)) {
            $this->redirect('admin/user/index');
        }
        $this->checkAuth();
        $this->getMenu();
    }
    /**
     * 权限检查
     * @return bool
     */
    protected function checkAuth()
    {

        if (!Session::has('user_id')) {
            $this->redirect('admin/login/index');
        }

        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        // 排除权限
        $not_check = ['admin/index/index','admin/index/welcome', 'admin/authgroup/getjson', 'admin/system/clear'];
        $url = $module . '/' . $controller . '/' . $action;
        $url = strtolower($url);
        if (!in_array($url, $not_check)) {
            $auth     = new Auth();
            $admin_id = Session::get('user_id');
            if (!$auth->check($url, $admin_id) && $admin_id != 1) {
                $this->error('没有权限',"{$module}/{$controller}/index");
            }
        }
    }
    
    /**
     * 获取侧边栏菜单
     */
    protected function getMenu()
    {
        $menu           = [];
        $admin_id       = Session::get('user_id');
        $group_id       = Session::get('group_id');
        $menu = session('menu/'.$group_id);
        if (empty($menu)) {
             $auth           = new Auth();
            $auth_rule_list = Db::name('auth_rule')->where('status', 1)->order(['sort' => 'DESC', 'id' => 'ASC'])->select();
            foreach ($auth_rule_list as $value) {
                if ($auth->check($value['name'], $admin_id) || $admin_id == 1) {
                    $menu[] = $value;
                }
            }
            $menu = !empty($menu) ? array2tree($menu) : [];
            session('menu/'.$group_id, $menu);
        }
       
        $this->assign('menu', $menu);
    }

    
}
