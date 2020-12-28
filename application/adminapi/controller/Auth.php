<?php

namespace app\adminapi\controller;

use app\common\model\Admin;
use app\common\model\Role;
use think\Collection;
use think\Request;
use app\common\model\Auth as AuthModel;

class Auth extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数 keyword  type
        $params = input();
        $condition = [];
        if ( ! empty($params['keyword'])){
            $condition['auth_name'] = ['like', "%{$params['keyword']}%"];
        }
        //查询数据
        $fields = 'id,auth_name,pid,pid_path,auth_c,auth_a,is_nav,level';
        $list = AuthModel::field($fields)->where($condition)->select();
        //转化为标准的二维数组
        $list = (new Collection($list))->toArray();
        if ( ! empty($params['type']) && $params['type'] == 'tree'){
            //父子级树状列表
            $list = get_tree_list($list);
        }else{
            //无限级分类列表
            $list = get_cate_list($list);
        }
        //返回数据
        $this->ok($list);
    }

    private function getParams(){
        //接收数据
        $params = input();
        //临时处理
        if (empty($params['pid'])){
            $params['pid'] = 0;
        }
        $params['is_nav'] = intval($params['radio']);
        //参数检测
        $validate = $this->validate($params, [
            'auth_name|权限名称' => 'require',
            'pid|上级权限' => 'require',
            'is_nav|菜单权限' => 'require',
            //'auth_c|控制器名称' => '',
            //'auth_a|方法名称' => '',
        ]);
        if ($validate !== true){
            $this->fail($validate, 401);
        }
        return $params;
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收数据处理
        $params = $this->getParams();
        //添加数据（是否顶级，级别和pid_path处理）
        if ($params['pid'] == 0){
            $params['level'] = 0;
            $params['pid_path'] = 0;
            $params['auth_c'] = '';
            $params['auth_a'] = '';
        }else{
            //不是顶级权限
            //查询上级信息
            $p_info = AuthModel::find($params['pid']);
            if(empty($p_info)){
                $this->fail('数据异常');
            }
            //设置级别+1  家族图谱 拼接
            $params['level'] = $p_info['level'] + 1;
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
        }
        //实际开发 可能不需要返回新增的这条数据
        //\app\common\model\Auth::create($params, true);
        //$this->ok();
        //restful 严格风格
        $auth = AuthModel::create($params, true);
        $info = AuthModel::find($auth['id']);
        //返回数据
        $this->ok($info);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //查询数据
        $fields = 'id,auth_name,pid,pid_path,auth_c,auth_a,is_nav,level';
        $auth = AuthModel::field($fields)->find($id);
        //返回数据
        $this->ok($auth);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //接收数据处理
        $params = $this->getParams();
        //修改数据（是否顶级，级别和pid_path处理）
        $auth = AuthModel::find($id);
        if(empty($auth)){
            $this->fail('数据异常');
        }
        if ($params['pid'] == 0){
            //如果修改顶级权限
            $params['level'] = 0;
            $params['pid_path'] = 0;
        }else if($params['pid'] != $auth['pid']){
            //如果修改其上级权限pid  重新设置level级别 和 pid_path 家族图谱
            $p_auth = AuthModel::find($params['pid']);
            if(empty($p_auth)){
                $this->fail('数据异常');
            }
            $params['level'] = $p_auth['level'] + 1;
            $params['pid_path'] = $p_auth['pid_path'] .'_'.$p_auth['id'];
        }
        AuthModel::update($params, ['id'=>$id], true);
        //返回数据
        $info = AuthModel::find($id);
        $this->ok($info);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //判断是否有子权限
        $total = AuthModel::where('pid', $id)->count();
        if($total > 0){
            $this->fail('有子权限，无法删除');
        }
        AuthModel::destroy($id);
        //返回数据
        $this->ok();
    }

    /**
     * 菜单权限
     */
    public function nav(){
        //获取登录的管理员用户id
        $user_id = input('user_id');
        //查询管理员的角色id   role_id
        $info = Admin::find($user_id);
        $role_id = $info['role_id'];
        //判断是否超级管理员
        if($role_id == 1){
            //超级管理员  直接查询权限表  菜单权限  is_nav = 1
            $data = AuthModel::where('is_nav', 1)->select();
        }else{
            //先查询角色表  role_auth_ids
            $role = Role::find($role_id);
            $role_auth_ids = $role['role_auth_ids'];
            //再查询权限表
            $data = AuthModel::where('is_nav',1)->where('id','in', $role_auth_ids)->select();
        }
        //先转化为标准的二维数组
        $data = (new Collection($data))->toArray();
        //再转化为 父子级树状结构
        $data = get_tree_list($data);
        //返回
        $this->ok($data);
    }
}
