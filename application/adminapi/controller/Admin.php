<?php

namespace app\adminapi\controller;

use app\common\model\Admin as AdminModel;

class Admin extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数
        $params = input();
        $condition = [];
        //搜索条件
        if ( ! empty($params['keyword'])){
            $keyword = $params['keyword'];
            $condition['username'] = ['like', "%$keyword%"];
        }
        //分页查询（包含搜索）
        $list = AdminModel::alias('t1')
                    ->join('pyg_role t2','t1.role_id=t2.id','left')
                    ->field('t1.*,t2.role_name')->where($condition)->paginate(30);
        //返回数据
        $this->ok($list);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save()
    {
//        $a = @file_get_contents('php://input');
//        dump($a);exit;
        //接收数据
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
           'username|用户名'=>'require|unique:admin',
           'email|邮箱'=>'require|email',
           'role_id|所属角色'=>'require|integer|gt:0',
           'password|密码'=>'length:6,20'
        ]);
        if ($validate !== true){
            $this->fail($validate);
        }
        //添加数据
        if (empty($params['password'])){
            $params['password'] = '111111';
        }
        $params['password'] = encrypt_password($params['password']);
        $params['nickname'] = $params['username'];
        $info = AdminModel::create($params, true);
        //查询刚才添加的完整的数据
        $admin = AdminModel::find($info['id']);
        //返回数据
        $this->ok($admin);
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
        $info = AdminModel::find($id);
        //返回数据
        $this->ok($info);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update($id)
    {
        $params = input();
        if(!empty($params['type']) && $params['type'] == 'reset_pwd'){
            $password = encrypt_password('111111');
            AdminModel::update(['password' => $password], ['id' => $id], true);
        }else{
            //参数检测
            $validate = $this->validate($params, [
                'email|邮箱' => 'email',
                'role_id|所属角色' => 'integer|gt:0',
                'nickname|昵称' => 'max:50'
            ]);
            if ($validate !== true){
                $this->fail($validate);
            }
            //修改数据（用户名不让改）
            unset($params['username']);
            unset($params['password']);
            AdminModel::update($params, ['id'=>$id], true);
        }
        //查询刚才添加的完整的数据
        $admin = AdminModel::find($id);
        //返回数据
        $this->ok($admin);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除数据（不能删除超级管理员admin、不能删除自己）
        if($id == 1){
            $this->fail('不能删除超级管理员');
        }
        if($id == input('user_id')){
            $this->fail('删除自己? 你在开玩笑嘛');
        }
        AdminModel::destroy($id);
        //返回数据
        $this->ok();
    }
}
