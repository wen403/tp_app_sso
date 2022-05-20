<?php

namespace app\rpc\controller;

use Hprose\Http\Server;

class Start
{
    protected $server      = null;
    protected $crossDomain = true;
    protected $p3p         = true;
    protected $debug       = true;

    public function run()
    {
        $param = request()->only([
            'c' => 'Start'
        ]);

        $class = "\\app\\rpc\\controller\\{$param['c']}";

        $this->server = new Server();
        $this->server->addInstanceMethods(app($class));
        $this->server->debug       = $this->debug;
        $this->server->p3p         = $this->p3p;
        $this->server->crossDomain = $this->crossDomain;
        $this->server->start();
    }
}