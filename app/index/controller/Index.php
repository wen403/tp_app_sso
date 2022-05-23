<?php

namespace app\index\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return view();
    }

    public function logout()
    {
        \app\common\Login::setOffline(session('user.id'));

        session(null);

        return json(['code' => 200, 'msg' => '登出成功！']);
    }
}
