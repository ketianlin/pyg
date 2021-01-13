<?php

namespace app\adminapi\controller;

use app\common\model\Brand as BrandModel;
use app\common\model\Goods;
use think\Image;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //接收参数  cate_id ;  keyword  page
        $params = input();
        $condition = [];
        if (isset($params['cate_id']) && !empty($params['cate_id'])){
            //分类下的品牌列表
            $condition['cate_id'] = $params['cate_id'];
            // 查询数据
            $list = BrandModel::where($condition)->field('id,name')->select();
        }else{
            //分页+搜索列表
            if (isset($params['keyword']) && !empty($params['keyword'])){
                $keyword = $params['keyword'];
                $condition['t1.name'] = ['like', "%$keyword%"];
            }
            $list = BrandModel::alias('t1')
                    ->join('pyg_category t2','t1.cate_id=t2.id','left')
                    ->field('t1.*,t2.cate_name')
                    ->where($condition)
                    ->paginate(30);
        }
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
            'name' => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        //生成缩略图  /uploads/brand/20190716/1232.jpg
        if(isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])){
            Image::open('.'.$params['logo'])->thumb(200,100)->save('.'.$params['logo']);
        }
        //添加数据
        $brand = BrandModel::create($params, true);
        $info = BrandModel::find($brand['id']);
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
        //查询一条记录
        $info = BrandModel::find($id);
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
            'name' => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot' => 'require|in:0,1',
            'sort' => 'require|between:0,9999'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        //修改数据（logo图片 缩略图）
        if(isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])){
            //生成缩略图
            //$params['logo']
            Image::open('.' . $params['logo'])->thumb(200, 100)->save('.' . $params['logo']);
        }
        BrandModel::update($params, ['id' => $id], true);
        $info = BrandModel::find($id);
        //返回数据
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
        //判断 品牌下是否有商品
        $total = Goods::where('brand_id', $id)->count();
        if($total > 0){
            $this->fail('品牌下有商品，不能删除');
        }
        //删除品牌
        BrandModel::destroy($id);
        //返回结果
        $this->ok();
    }
}
