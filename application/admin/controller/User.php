<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Validate;

class User extends Controller
{
    public function userlist()
    {
        $data = Db::name('User')
            ->order('id asc')
            ->paginate(15);
        $this->assign('users', $data);
        return $this->fetch();
    }
    //打开新增界面
    public function showAdd()
    {
        $auth_group = Db::name('auth_group')
        ->field('id,title')
        ->order('id desc')
        ->select();
        return $this->fetch('add',['auth_group'=>$auth_group]);
    }
    //增加用户
    public function addUser()
    {
        $post     = $this->request->post();
        $group_id = $post['group_id'];
        unset($post['group_id']);
        $validate = validate('User');
        $res      = $validate->check($post);
        if ($res !== true) {
            $this->error($validate->getError());
        } else {
            unset($post['check_password']);
            $post['password'] = md5($post['password']);
            $post['last_login_ip'] = '0.0.0.0';
            $post['create_time']   = date('Y-m-d h:i:s', time());
            $db = Db::name('user')
                ->insert($post);
            $userId = Db::name('user')->getLastInsID();
             Db::name('auth_group_access')
                ->insert(['uid'=>$userId,'group_id'=>$group_id]);
            $this->success('success');
        }
    }
    //编辑页面
    public function edit($id)
    {
        
        $data = Db::name('User')
            ->alias('a')
            ->join('auth_group_access b','b.uid=a.id','left')
            ->field('a.*,b.group_id')
            ->where('id', $id)
            ->find();
        $auth_group = Db::name('auth_group')
        ->field('id,title')
        ->order('id desc')
        ->select();
        $this->assign('auth_group', $auth_group);
        $this->assign('data', $data);
        return $this->fetch();
    }
    //编辑提交
    public function editUser()
    {
        $post     = $this->request->post();
        if($post['id']==1){
            $this->error('系统管理员无法修改');
        }
        $group_id = $post['group_id'];
        unset($post['group_id']);
        $validate = validate('User');
        if (empty($post['password']) && empty($post['check_password'])) {
            $res = $validate->scene('edit')->check($post);
            if ($res !== true) {
                $this->error($validate->getError());
            } else {
                unset($post['password']);
                unset($post['check_password']);
                $db = Db::name('user')
                    ->where('id', $post['id'])
                    ->update(
                        [
                            'username' => $post['username'],
                            'email'    => $post['email'],
                        ]);
                Db::name('auth_group_access')
                ->where('uid',$post['id'])
                ->update(['group_id'=>$group_id]);
                $this->success('编辑成功');
            }
        } else {
            $res = $validate->scene('editPassword')->check($post);
            if ($res !== true) {
                $this->error($validate->getError());
            } else {
                unset($post['check_password']);
                $post['password'] = md5($post['password']);
                $db               = Db::name('user')
                    ->where('id', $post['id'])
                    ->update($post);
                $this->success('编辑成功');
            }
        }
    }
    //删除用户
    public function deleteUser()
    {
        $id = $this->request->post('id');
        $username =  Db::name('user')
            ->where('id',$id)
            ->value('username');
        if ((int) $id !== 1) {
            if($username!==session('username')){
                 $db = Db::name('user')
                ->where('id', $id)
                ->delete();
                $this->success('删除成功');
            }else{
                 $this->error('无法删除当前登录用户');
            }
        } else {
            $this->error('超级管理员无法删除');
        }
    }

}
