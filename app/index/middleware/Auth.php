<?php
declare (strict_types=1);

namespace app\index\middleware;

use app\common\Login;
use app\Request;
use Closure;
use think\Exception;

class Auth
{
    /**
     * 处理请求
     * @throws /Exception
     */
    public function handle(Request $request, Closure $next)
    {
        // 携带了 token
        if (input('?token')) {
            // jwt 解码
            try {
                $authInfo = Login::decodeJWT(input('token'));
                session('user', [
                    'id'       => $authInfo['id'],
                    'username' => $authInfo['username'],
                    'token'    => input('token'),
                ]);
                return redirect('/');
            } catch (\Exception $e) {
                throw new Exception('token 验证失败:' . $e->getMessage());
            }
        }

        $cont = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($cont[2]) && $cont[2] === 'login') {
            return $next($request);
        }

        // 检测到用户没有登录
        // 如果 session 中没有 user 和 sso 就代表没有登录
        if (!session('?user')) {
            // 跳转到登录页面
            return redirect('/index/login/index');
        }

        return $next($request);
    }
}
