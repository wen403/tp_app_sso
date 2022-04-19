<?php
declare (strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

class Server
{
    protected $server      = null;
    protected $crossDomain = true;
    protected $p3p         = true;
    protected $debug       = true;

    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $module     = strtolower(app('http')->getName());
        $controller = strtolower(request()->controller());

        if ($module) {
            $m = "app\\$module\controller\\$controller";
        } else {
            $m = "app\\controller\\$controller";
        }

        $this->server = new \Hprose\Http\Server();
        $this->server->addInstanceMethods(app($m));
        $this->server->debug       = $this->debug;
        $this->server->p3p         = $this->p3p;
        $this->server->crossDomain = $this->crossDomain;
        $this->server->start();

        return $response;
    }

}
