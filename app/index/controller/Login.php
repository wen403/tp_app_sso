<?php
declare (strict_types=1);

namespace app\index\controller;

use app\BaseController;
use think\exception\HttpResponseException;
use think\facade\Cache;

class Login extends BaseController
{
    public function initialize()
    {
        // 已登录
        if (session('?user') && request()->action() !== 'logout') {
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

            // 验证数据
            $id = \app\common\Login::validate($data);
            if ($id === false) {
                return json(['code' => 400, 'msg' => '用户不存在']);
            }
            // 记录用户信息
            $data['id'] = $id;
            $token      = \app\common\Login::encodeJWT($data);
            $mark       = ['id' => $id, 'username' => $data['username'], 'token' => $token];
            session('user', $mark);

            // 上线
            \app\common\Login::setOnline($id, $data['username'], $token);

            return json(['code' => 200, 'msg' => '登录成功']);
        }

        return view();
    }

    public function register()
    {
        $param = request()->only([
            'username'    => '',
            'password'    => '',
            're_password' => ''
        ], 'post', 'trim');

        if (empty($param['username'])) {
            return json(['code' => 400, 'msg' => '用户不能为空']);
        }

        if (empty($param['password'])) {
            return json(['code' => 400, 'msg' => '密码不能为空']);
        }

        if ($param['password'] <> $param['re_password']) {
            return json(['code' => 400, 'msg' => '密码不一致']);
        }

        $user = Cache::store('redis')->get('user');

        if (empty($user)) {
            Cache::store('redis')->set('user', [
                ['id' => 1, 'name' => $param['username'], 'passowrd' => $param['password']]
            ]);
        } else {
            $username = array_column($user, 'name');
            if (in_array($param['username'], $username)) {
                return json(['code' => 200, 'msg' => '你是不是注册过']);
            }
            $user[] = ['id' => count($user) + 1, 'name' => $param['username'], 'password' => $param['password']];
            Cache::store('redis')->set('user', $user);
        }

        return json(['code' => 200, 'msg' => '注册成功']);
    }

    public function logout()
    {
        \app\common\Login::setOffline(session('user.id'));

        session(null);

        return json(['code' => 200, 'msg' => '登出成功！']);
    }
}
