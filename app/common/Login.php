<?php

namespace app\common;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Cache;

class Login
{
    /**
     * jwt 解码
     */
    public static function decodeJWT($token)
    {
        return (array)JWT::decode($token, new Key('1321994008@qq.com', 'HS256'));
    }

    /**
     * 登录加密生成 token
     */
    public static function encodeJWT($data)
    {
        // JWT 加密
        return JWT::encode([
            'id'       => $data['id'],
            'username' => $data['username'],
            'iss'      => 'sso.test',
            'aud'      => 'wzp',
        ], '1321994008@qq.com', 'HS256');
    }

    /**
     * 登录成功后在 redis 上线
     */
    public static function setOnline($id, $name, $token)
    {
        $online_user = Cache::store('redis')->get('online_user');
        $data        = ['id' => $id, 'username' => $name, 'token' => $token];
        if (empty($online_user)) {
            Cache::store('redis')->set('online_user', [$data]);
        } else {
            // 去重
            $arrUser = array_column($online_user, 'username');
            if (!in_array($name, $arrUser)) {
                $online_user[] = $data;
                Cache::store('redis')->set('online_user', $online_user);
            }
        }
    }

    /**
     * 登出后下线 redis
     */
    public static function setOffline($id)
    {
        $online_user = Cache::store('redis')->get('online_user');

        foreach ($online_user as $key => $value) {
            if ($value['id'] == $id) {
                unset($online_user[$key]);
                break;
            }
        }

        Cache::store('redis')->set('online_user', $online_user);
    }

    /**
     * 用户登录验证
     */
    public static function validate($data)
    {
        $user     = Cache::store('redis')->get('user');
        $arr_user = array_column($user, 'name', 'id');
        $id       = array_search($data['username'], $arr_user);
        if ($id === false) {
            return false;
        }
        return $id;
    }
}