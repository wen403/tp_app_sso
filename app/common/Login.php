<?php

namespace app\common;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
            'username' => $data['username'],
            'iss'      => 'sso.test',
            'aud'      => 'wzp',
        ], '1321994008@qq.com', 'HS256');
    }
}