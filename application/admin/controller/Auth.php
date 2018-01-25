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

class auth extends Controller
{
    public $operates = [
        'add' => '添加',
        'edit' => '修改',
        'delete' => '作废',
        'check' => '审核',
        'printf' => '打印',
        'store' => '商品'
    ];

    function index(){
        //获取权限列表
        $auth = Db::name('auth_rule')->order(['sort' => 'DESC', 'id' => 'ASC'])->select();
        $auth = array2Level($auth);
        return $this->view->fetch('index',['auth'=>$auth]);
    }
    function showAdd(){
    	$auth = Db::name('auth_rule')->where('icon', '<', 3)->order(['sort' => 'DESC', 'id' => 'ASC'])->select();
        $auth = array2Level($auth);
        $this->view->assign('operates', $this->operates);
    	return  $this->view->fetch('add',['auth'=>$auth]);
    }
    function add(){
    	$post = $this->request->post();
    	$validate = validate('auth');
    	$res = $validate->check($post);
		if($res!==true){
			return ajax_return_error($validate->getError());
		}else{
            $inserts = [];
            $module = $this->request->module();
            $name = strtolower($post['name']);
            if (stripos($name, '/')  === false) {
                if ($post['pid'] > 0){
                    $post['name'] = $module . '/' . $post['name'] .'/index';
                    $post['operate'][] = 'recyclebin';
                    $this->operates['recyclebin'] = '回收站';
                }else{
                    $post['name'] = $module . '/' . $post['name'] .'/default';
                }
            }
            $operate = !empty($post['operate']) ?  $post['operate']: [];
            unset($post['operate']);
            $id = Db::name('auth_rule')->insertGetId($post);
            if ($id < 0) {
                return ajax_return_error('操作失败！');
            }
            // 添加操作子菜单
            if (!empty($operate)) {
                ++$post['icon'];
                foreach ($operate as $value) {
                    $insert = [];
                    $insert['name'] = $module . '/' . $name . '/' . $value;
                    $insert['title'] = $this->operates[$value];
                    $insert['status'] = $post['status'];
                    $insert['status'] = $post['status'];
                    $insert['pid'] = $id;
                    $insert['icon'] = $post['icon'];
                    $insert['sort'] = $post['sort'];
                    $inserts[] = $insert;
                }
                $res = Db::name('auth_rule')->insertAll($inserts);
                if($res){
                    return ajax_return_adv('success');
                }else{
                    return ajax_return_error('error');
                }
            }
            
			return ajax_return_adv('success');
		}
    }
    function showEdit(){
        $id  = $this->request->get('id');
        $pid = Db::name('auth_rule')
            ->where('id',$id)
            ->value('pid');
        if($pid!==0){
            $p_title = Db::name('auth_rule')
                ->where('id',$pid)
                ->value('title');
        }else{
            $p_title = '顶级菜单';
        }
        $this->view->assign('p_title',$p_title);
        $data  =   Db::name('auth_rule')
            ->where('id',$id)
            ->find();
        return  $this->view->fetch('edit',['data'=>$data]);
    }
    function edit(){
        $post =  $this->request->post();
        $id = $post['id'];
        if($id<4){
            return ajax_return_error('很抱歉,系统默认权限无法编辑');
        }
        $validate = validate('auth');
        $validate->scene('edit');
        $res = $validate->check($post);
        if($res!==true){
            return ajax_return_error($validate->getError());
        }else{
            unset($post['id']);
            $res = Db::name('auth_rule')
            ->where('id',$id)
            ->update($post);
            if ($res) {
                return ajax_return_adv('success');  
            }else{
                return ajax_return_error('操作失败！');  
            }        
        }
    }
    function delete(){
        $id = $this->request->post('id');
        $juge = Db::name('auth_rule')
            ->where('pid',$id)
            ->find();
        if($id<7){
                return ajax_return_error('重要节点无法删除'); 
        }
        if(!empty($juge)){ 
               return ajax_return_error('请先删除子权限'); 
        }else{
            if($id<10){
                 return ajax_return_error('重要节点无法删除'); 
            }else{
                 Db::name('auth_rule')
                    ->delete($id);
                    return ajax_return_adv('success');
            }
        }
    }
    function showRole(){
        $role = Db::name('auth_group')
            ->order('id desc')
            ->paginate('15');
        $this->view->assign('role',$role);
        return $this->view->fetch('role');
    }
    function addRole(){
        $auth_group = $this->request->post('role_name');
        if(!empty($auth_group)){
            $res = Db::name('auth_group')
            ->where('title',$auth_group)
            ->find();
            if(empty($res)){
                 Db::name('auth_group')
                ->insert(['title'=>$auth_group]);
                return ajax_return_adv('添加成功');
            }else{
                return ajax_return_error('系统中已经存在该用户名');  
            }
        }else{
            return ajax_return_error('请输入角色名称再添加');
        }
    }
    function showAuth($id){
        $this->view->assign('id',$id);
        return $this->view->fetch('auth');
    }
    //获取规则数据
    public function getJson()
    {   
        $id = $this->request->post('id');
        $auth_group_data = Db::name('auth_group')->find($id);
        $auth_rules      = explode(',', $auth_group_data['rules']);
        $auth_rule_list  = Db::name('auth_rule')->field('id,pid,title')->select();

        foreach ($auth_rule_list as $key => $value) {
            in_array($value['id'], $auth_rules) && $auth_rule_list[$key]['checked'] = true;
        }
        return $auth_rule_list;
    }
    /**
     * 更新权限组规则
     * @param $id
     * @param $auth_rule_ids
     */
    public function updateAuthGroupRule()
    {
        if ($this->request->isPost()){
            $post = $this->request->post();
            if($post['id']==1){
               //$this->error('超级管理员信息无法编辑'); 
            }
            $group_data['id']    = $post['id'];
            $group_data['rules'] = is_array($post['auth_rule_ids']) ? implode(',', $post['auth_rule_ids']) : '';
            if (Db::name('auth_group')->where('id',$post['id'])->update($group_data) !== false) {
               return ajax_return_adv('授权成功');
            } else {
               return ajax_return_error('授权失败');
            }
        }
    }
    function showRoleEdit($id){
        $data = Db::name('auth_group')
        ->where('id',$id)
        ->find();
        return $this->view->fetch('roleEdit',['data'=>$data]);
    }
    function editRole(){
        $post = $this->request->post();
        if($post['id']==1){
           return ajax_return_error('超级管理员信息无法编辑'); 
        }
        $validate = validate('role');
        $res = $validate->check($post);
        if(!$res){
            return ajax_return_error($validate->getError());
        }else{
            Db::name('auth_group')
            ->where('id',$post['id'])
            ->update(['title'=>$post['title'],'status'=>$post['status']]);
           return ajax_return_adv('更新成功');
        }
    }
    function delRole(){
        $id  = $this->request->post('id');
        if($id!=='1'){
            $res =  Db::name('auth_group')
            ->delete($id);
           return ajax_return_adv('删除成功');
        }else{
           return ajax_return_error('超级管理员无法删除');
        }
    }

}
