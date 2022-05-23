<?php

namespace app\sso\controller;

use app\BaseController;

class Login extends BaseController
{
    public function index()
    {
        // 携带了 token
        if (input('?token')) {
            // jwt 解码
            $authInfo = \app\common\Login::decodeJWT(input('token'));
            $user     = $authInfo['username'];

            // 验证
            $id = \app\common\Login::validate($authInfo);
            if ($id === false) {
                redirect(input('url'));
            }

            // 上线
            \app\common\Login::setOnline($id, $user, input('token'));

            session('user', [
                'id'       => $id,
                'username' => $user,
                'token'    => input('token'),
            ]);

            return redirect(input('url'));
        }

        // 没有登录
        if (!session('?user')) {
            return redirect(input('url'));
        }

        // 验证成功跳转回去
        $url = parse_url(input('url'));

        return redirect($url['scheme'] . '://' . $url['host'] . '?token=' . urlencode(session('user.token')));
    }

    public function logout()
    {
        $param = request()->only([
            'url' => '',
        ], 'get', 'trim');

        \app\common\Login::setOffline(session('user.id'));

        session(null);

        if (empty($param['url'])) {
            return redirect('/');
        }

        return redirect($param['url']);
    }
}