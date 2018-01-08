<?php

//------------------------
// 公开不授权控制器
//-------------------------

namespace app\admin\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\Loader;
use think\Session;
use think\Db;
use think\Config;
use think\Exception;
use think\View;
use think\Request;

class Pub
{
    use \traits\controller\Jump;

    // 视图类实例
    protected $view;
    // Request实例
    protected $request;

    public function __construct()
    {
        if (null === $this->view) {
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        }
        if (null === $this->request) {
            $this->request = Request::instance();
        }

        // 用户ID
        defined('UID') or define('UID', Session::get(Config::get('auth.user_auth_key')));
    }

    /**
     * 检查用户是否登录
     */
    protected function checkUser()
    {
        if (null === UID) {
            if ($this->request->isAjax()) {
                ajax_return_adv_error("登录超时，请先登陆", "", "", "current", url("loginFrame"))->send();
            } else {
                $this->error("登录超时，请先登录", Config::get('auth.user_auth_gateway'));
            }
        }

        return true;
    }

    /**
     * 用户登录页面
     * @return mixed
     */
    public function login()
    {
        if (Session::has(Config::get('auth.user_auth_key'))) {
            $this->redirect('Index/index');
        } else {
            return $this->view->fetch();
        }
    }

    /**
     * 小窗口登录页面
     * @return mixed
     */
    public function loginFrame()
    {
        return $this->view->fetch();
    }

    /**
     * 首页
     */
    public function index()
    {
        // 如果通过认证跳转到首页
        $this->redirect("Index/index");
    }

    /**
     * 用户登出
     */
    public function logout()
    {
        if (UID) {
            Session::clear();
            $this->success('退出成功！', Config::get('auth.user_auth_gateway'));
        } else {
            $this->error('已经退出！', Config::get('auth.user_auth_gateway'));
        }
    }

    /**
     * 登录检测
     * @return \think\response\Json
     */
    public function checkLogin()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $data = $this->request->post();
            $validate = Loader::validate('User');
            if (!$validate->scene('login')->check($data)) {
                return ajax_return_adv_error($validate->getError());
            }

            $map['account'] = $data['username'];
            $map['status'] = 1;
            $auth_info = DB::name('user')
                            ->alias('a')
                            ->join('auth_group_access b', 'a.id=b.uid', 'left')
                            ->field('a.username,a.id,a.password,a.realname,a.last_login_ip,a.last_login_time,b.group_id')
                            ->where('username', $data['username'])->find();
            // 使用用户名、密码和状态的方式进行认证
            if (null === $auth_info) {
                return ajax_return_adv_error('帐号不存在或已禁用！');
            } else {
                if ($auth_info['password'] != password_hash_tp($data['password'])) {
                    return ajax_return_adv_error('密码错误！');
                }

                // 生成session信息
                Session::set(Config::get('auth.user_auth_key'), $auth_info['id']);
                Session::set('user_name', $auth_info['username']);
                Session::set('real_name', $auth_info['realname']);
                Session::set('group_id', $auth_info['group_id']);
                Session::set('last_login_ip', $auth_info['last_login_ip']);
                Session::set('last_login_time', $auth_info['last_login_time']);

                // 超级管理员标记
                if ($auth_info['id'] == 1) {
                    Session::set(Config::get('auth.admin_auth_key'), true);
                }

                // 保存登录信息
                $update['last_login_time'] = date('Y-m-d H:i:s', time());
                $update['login_count'] = ['exp', 'login_count+1'];
                $update['last_login_ip'] = $this->request->ip();
                Db::name("user")->where('id', $auth_info['id'])->update($update);

                // 记录登录日志
                $log['uid'] = $auth_info['id'];
                $log['login_ip'] = $this->request->ip();
                $log['login_location'] = implode(" ", \Ip::find($log['login_ip']));
                $log['login_browser'] = \Agent::getBroswer();
                $log['login_os'] = \Agent::getOs();
                Db::name("LoginLog")->insert($log);
                $auth = new \org\Auth();
                // 缓存访问权限
                $auth->saveAccessList($auth_info['id'], $auth_info['group_id']);
                return ajax_return_adv('登录成功！', '');
            }
        } else {
            throw new Exception("非法请求");
        }
    }

    /**
     * 修改密码
     */
    public function password()
    {
        $this->checkUser();
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据校验
            $validate = Loader::validate('Pub');
            if (!$validate->scene('password')->check($data)) {
                return ajax_return_adv_error($validate->getError());
            }

            // 查询旧密码进行比对
            $info = Db::name("user")->where("id", UID)->field("password")->find();
            if ($info['password'] != password_hash_tp($data['oldpassword'])) {
                return ajax_return_adv_error("旧密码错误");
            }

            // 写入新密码
            if (false === Loader::model('user')->updatePassword(UID, $data['password'])) {
                return ajax_return_adv_error("密码修改失败");
            }

            return ajax_return_adv("密码修改成功", '');
        } else {
            return $this->view->fetch();
        }
    }

    /**
     * 查看用户信息|修改资料
     */
    public function profile()
    {
        $this->checkUser();
        if ($this->request->isPost()) {
            // 修改资料
            $data = $this->request->only(['realname', 'email', 'mobile', 'remark'], 'post');
            if (Db::name("user")->where("id", UID)->update($data) === false) {
                return ajax_return_adv_error("信息修改失败");
            }

            return ajax_return_adv("信息修改成功", '');
        } else {
            // 查看用户信息
            $vo = Db::name("user")->field('realname,email,mobile,remark')->where("id", UID)->find();
            $this->view->assign('vo', $vo);

            return $this->view->fetch();
        }
    }
}
