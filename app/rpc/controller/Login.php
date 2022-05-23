<?php
declare (strict_types=1);

namespace app\rpc\controller;

use app\BaseController;
use Exception;
use think\facade\Cache;

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
        } catch (Exception $e) {
            return json_encode(['code' => 400, 'msg' => $e->getMessage()]);
        }

        return json_encode(['code' => 200, 'msg' => '登录成功', 'data' => [
            'id'       => $authInfo['id'],
            'username' => $authInfo['username'],
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

        // 验证数据
        $id = \app\common\Login::validate($data);
        if ($id === false) {
            return json_encode(['code' => 400, 'msg' => '用户不存在']);
        }

        // 上线
        $data['id'] = $id;
        $token      = \app\common\Login::encodeJWT($data);
        \app\common\Login::setOnline($id, $data['username'], $token);

        return json_encode(['code' => 200, 'msg' => '登录成功', 'token' => $token, 'id' => $id]);
    }

    /**
     * 用户是否在线
     */
    public function isOnline($id)
    {
        $onlineUser = Cache::store('redis')->get('online_user');
        if (empty($onlineUser)) {
            return false;
        }
        $arrUser = array_column($onlineUser, 'id');
        if (!in_array($id, $arrUser)) {
            return false;
        }
        return true;
    }
}
