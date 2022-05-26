<?php
declare (strict_types=1);

namespace app\command;

use Redis;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;

class RedisCallback extends Command
{
    public static function keycallback($redis, $pattern, $channel, $msg)
    {
        echo "已删除订单编号" . $msg . "\n\n";
        /*TODO处理业务逻辑*/
    }

    protected function configure()
    {
        // 指令配置
        $this->setName('callback')->setDescription('redis过期回调');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('进命令了');
        //设置redis超时时间 -1永不超时
        Cache::store('redis')->handler()->setOption(Redis::OPT_READ_TIMEOUT, -1);
        //订阅redis 1 库的过期事件，触发app\command\RedisCallback::keycallback命令
        Cache::store('redis')->handler()->psubscribe(['__keyevent@*__:expired'], 'app\command\RedisCallback::keycallback');

        $output->writeln('回调完了');
    }
}

