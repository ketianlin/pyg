<?php
namespace app\adminapi\controller;

use app\common\model\Profile;
use think\Db;
use tools\jwt\Token;

class Index extends BaseApi
{
    public function index()
    {
        //一对多关联
        //以分类表为主
        $info = \app\common\model\Category::with('brands')->find(72);
        $this->ok($info);

//        $info = \app\common\model\Category::with('abc')->find(72);
//        $this->ok($info);

//        $info = \app\common\model\Category::with('brands')->find(72);
//        $this->ok($info);

//        $info = \app\common\model\Brand::with('category')->find(1);
//        $this->ok($info);

//        $info = \app\common\model\Admin::with('abc')->find(1);
//        $this->ok($info);

//        $info = Profile::with('admin')->find(1);
//        $this->ok($info);

//        $info = Profile::with('aaa')->find(1);
//        $this->ok($info);

//        $token = Token::getToken('fuck');
//        dump($token);
//        $userId = Token::getUserId($token);
//        dump($userId);exit;

//        $goods = Db::table('pyg_goods')->find();
//        $this->ok($goods);
//        return 'adminapi的index的index';
    }
}
