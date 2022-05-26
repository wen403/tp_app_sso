<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace app\webSocket;

use Exception;
use GatewayWorker\Lib\Gateway;
use think\worker\Application;
use Workerman\Worker;

/**
 * Worker 命令行服务类
 */
class Events
{
    /**
     * onWorkerStart 事件回调
     * 当businessWorker进程启动时触发。每个进程生命周期内都只会触发一次
     */
    public static function onWorkerStart(Worker $businessWorker)
    {
        $app = new Application;
        $app->initialize();
    }

    /**
     *  onConnect 事件回调
     *  当客户端连接上gateway进程时(TCP三次握手完毕时)触发
     * @throws Exception
     */
    public static function onConnect($client_id)
    {
        // 给当前连接人发送连接信息
        Gateway::sendToCurrentClient(json_encode([
            'type' => 'connect',
            'time' => date('Y-m-d H:i:s'),
        ]));
    }

    /**
     * onMessage 事件回调
     * 当客户端发来数据(Gateway进程收到数据)后触发
     * @throws Exception
     */
    public static function onMessage($client_id, $data)
    {
        $data = json_decode($data, true);

        switch ($data['type']) {
            case 'login':
                Gateway::bindUid($client_id, $data['uid']);
                // 发送统计数据
                self::sendCountMsg();
                break;
            case 'msg_text':
                // 获取自己的所有连接id
                $clients = Gateway::getClientIdByUid($data['uid']);
                Gateway::sendToAll(json_encode($data), null, $clients);
                $data['type'] = 'msg_count';
                Gateway::sendToAll(json_encode($data));
                break;
        }
    }

    /**
     * 发送统计数据
     */
    protected static function sendCountMsg()
    {
        $openPage = Gateway::getAllClientCount();
        if (strlen(floor($openPage)) <= 1) {
            $openPage = '0' . $openPage;
        }
        if (strlen(floor($openPage)) >= 3) {
            $openPage = 99;
        }
        $onlineUsers = Gateway::getAllUidCount();
        if (strlen(floor($onlineUsers)) <= 1) {
            $onlineUsers = '0' . $onlineUsers;
        }
        if (strlen(floor($onlineUsers)) >= 3) {
            $onlineUsers = 99;
        }
        Gateway::sendToAll(json_encode([
            'type'        => 'count',
            'openPage'    => $openPage,
            'onlineUsers' => $onlineUsers
        ]));
    }

    /**
     * onClose 事件回调 当用户断开连接时触发的方法
     * 断开连接的客户端client_id
     */
    public static function onClose($client_id)
    {
        // 发送统计数据
        self::sendCountMsg();
    }
}
