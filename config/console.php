<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
use app\command\RedisCallback;

return [
    // 指令定义
    'commands' => [
        'callback' => RedisCallback::class
    ],
];
