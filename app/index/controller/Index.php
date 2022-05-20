<?php

namespace app\index\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return 'sso主页面用户' . session('user.name');
    }

}
