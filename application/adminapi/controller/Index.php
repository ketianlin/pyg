<?php
namespace app\adminapi\controller;

use think\Db;
use tools\jwt\Token;

class Index extends BaseApi
{
    public function index()
    {
//        $token = Token::getToken('fuck');
//        dump($token);
//        $userId = Token::getUserId($token);
//        dump($userId);exit;
        $goods = Db::table('pyg_goods')->find();
        $this->ok($goods);
        return 'adminapi的index的index';
    }
}
