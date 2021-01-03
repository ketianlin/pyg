<?php
namespace app\adminapi\controller;

use app\common\model\Category as CategoryModel;
use think\Collection;

class Category extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收pid参数  影响查询的数据
        //接收type参数  影响返回的数据
        $params = input();
        $where = [];
        $pid = 0;
        if(isset($params['pid'])){
            $where['pid'] = $params['pid'];
            $pid = $params['pid'];
        }

        //查询数据
        $list = CategoryModel::where($where)->select();
        //转化为标准二维数组结构
        $list = (new Collection($list))->toArray();
        /*if(isset($params['type']) && $params['type'] == 'list'){

        }else{
            $list = get_cate_list($list);
        }*/
        if(!isset($params['type']) || $params['type'] != 'list'){
            //转化为无限级分类列表
            $list = get_cate_list($list,$pid);
        }
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
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'cate_name' => 'require|length:2,20',
            'pid' => 'require|integer|egt:0',
            'is_show' => 'require|in:0,1',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        //添加数据(处理pid_path  pid_path_name  level)
        if($params['pid'] == 0){
            //顶级分类
            $params['pid_path'] = 0;
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        }else {
            //不是顶级分类，查询其上级分类
            $p_info = CategoryModel::find($params['pid']);
            if(empty($p_info)){
                //没查到父级
                $this->fail('数据异常,请稍后再试');
            }
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'] . '_' . $p_info['cate_name'];
            $params['level'] = $p_info['level'] + 1;
        }
        //logo图片处理
        $params['image_url'] = isset($params['logo']) ? $params['logo'] : '';
        //$params['image_url'] = $params['logo'] ?? '';
        $cate = CategoryModel::create($params, true);
        $info = CategoryModel::find($cate['id']);
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
        $info = CategoryModel::find($id);
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
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params, [
            'cate_name' => 'require|length:2,20',
            'pid' => 'require|integer|egt:0',
            'is_show' => 'require|in:0,1',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999',
        ]);
        if($validate !== true){
            $this->fail($validate);
        }

        //修改数据（处理pid_path pid_path_name level  image_url）
        if($params['pid'] == 0){
            //顶级分类
            $params['pid_path'] = 0;
            $params['pid_path_name'] = '';
            $params['level'] = 0;
        }else{
            //不是顶级分类，查询其上级分类
            $p_info = CategoryModel::find($params['pid']);
            if(empty($p_info)){
                //没查到父级
                $this->fail('数据异常,请稍后再试');
            }
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'] . '_' . $p_info['cate_name'];
            $params['level'] = $p_info['level'] + 1;
        }
        if(isset($params['logo']) && !empty($params['logo'])){
            $params['image_url'] = $params['logo'];
        }
        CategoryModel::update($params, ['id' => $id], true);
        //返回数据
        $info = CategoryModel::find($id);
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
        //删除数据
        //判断分类下是否有子分类
        $total = CategoryModel::where('pid', $id)->count();
        if($total > 0){
            $this->fail('分类下有子分类,无法删除');
        }
        CategoryModel::destroy($id);
        //返回数据
        $this->ok();
    }
}
