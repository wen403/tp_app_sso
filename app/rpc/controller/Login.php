<?php
declare (strict_types=1);

namespace app\rpc\controller;

use app\BaseController;
use Exception;

class Login extends BaseController
{
    /**
     * 验证 token
     */
    public function validateToken($token)
    {
        try {
            // jwt 解码
            $authInfo = \app\common\Login::decodeJWT($token);
            $user     = $authInfo['username'];
            session('user', [
                'username' => $user,
                'token'    => $token,
            ]);
        } catch (Exception $e) {
            return json_encode(['code' => 400, 'msg' => $e->getMessage()]);
        }

        return json_encode(['code' => 200, 'msg' => '登录成功', 'data' => [
            'username' => $user,
            'token'    => $token,
        ]]);
    }

    /**
     * 用户登陆
     * s     */
    public function login($data)
    {
        if (empty($data['username'])) {
            return json_encode(['code' => 400, 'msg' => '用户名不能为空']);
        }

        $token = \app\common\Login::encodeJWT($data);

        return json_encode(['code' => 200, 'msg' => '登录成功', 'token' => $token]);
    }
}
