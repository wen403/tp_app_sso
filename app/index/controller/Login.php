<?php
declare (strict_types=1);

namespace app\index\controller;

use app\BaseController;
use think\exception\HttpResponseException;

class Login extends BaseController
{
    public function initialize()
    {
        // 已登录
        if (session('?user')) {
            // 抛出 http 异常
            throw new HttpResponseException(redirect('/'));
        }
    }

    public function index()
    {
        if (request()->isPost() && request()->isAjax()) {
            $data = request()->only([
                'username' => null
            ], 'post', 'trim');

            if (empty($data['username'])) {
                return json(['code' => 400, 'msg' => '用户名不能为空']);
            }

            $token = \app\common\Login::encodeJWT($data);

            // 记录用户信息
            session('user', ['id' => time() + rand(), 'name' => $data['username'], 'token' => $token]);

            return json(['code' => 200, 'msg' => '登录成功', 'url' => '/']);
        }

        return view();
    }
}
