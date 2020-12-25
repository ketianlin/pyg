<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::domain('adminapi', function(){
    Route::get('/', 'adminapi/index/index');
//    Route::resource('goods', 'adminapi/goods');
    Route::get('captcha/:id', '\\think\\captcha\\CaptchaController@index');//访问图片需要
    //验证码图片
    Route::get('captcha', 'adminapi/login/captcha');
    //登录
    Route::post('login', 'adminapi/login/login');
    //退出
    Route::get('logout', 'adminapi/login/logout');
});
