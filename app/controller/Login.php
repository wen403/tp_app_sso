<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Login extends BaseController
{
    public function index()
    {
        // 判断用户是否携带 token
        if (input('?token')) {
            $this->decodeJWT(input('token'));
            return redirect(input('url'));
        }

        // 判断用户是否没有登录
        if (!session('?user')) {
            return redirect(input('url'));
        }

        // 验证成功跳转回去
        $url = parse_url(input('url'));

        return redirect($url['scheme'] . '://' . $url['host'] . '?token=' . urlencode(session('user.token')));
    }

    public function decodeJWT($token = '')
    {
        try {
            // jwt 解码
            $authInfo = (array)JWT::decode($token, new Key('1321994008@qq.com', 'HS256'));
            $user     = $authInfo['username'];
            session('user', [
                'username' => $user,
                'token'    => $token,
            ]);
        } catch (Exception $e) {
            return json_encode(['code' => 400, 'msg' => 'token 无效']);
        }

        return json_encode(['code' => 200, 'msg' => '登录成功', 'data' => [
            'username' => $user,
            'token'    => $token,
        ]]);
    }

    public function login($data = '')
    {
        if (empty($data['username'])) {
            return json_encode(['code' => 400, 'msg' => '用户名不能为空']);
        }

        // JWT 加密
        $token = JWT::encode([
            'username' => $data['username'],
            'iss'      => 'sso.test',
            'aud'      => 'wzp',
        ], '1321994008@qq.com', 'HS256');

        // 记录用户信息
        session('user', ['id' => time() + rand(), 'name' => $data['username'], 'token' => $token]);

        return json_encode(['code' => 200, 'msg' => '登录成功', 'token' => $token]);
    }
}
