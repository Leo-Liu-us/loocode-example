<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang01
 * Date: 2018/8/17
 * Time: 16:17
 */

namespace Landowner;


use Surf\Server\RedisConstant;
use Surf\Server\WebSocket\WebSocketServer;
use Surf\Ticker\Ticker;

class HeartbeatTicker extends Ticker
{

    /**
     *
     */
    public function execute()
    {
        // TODO: Implement execute() method.
        echo "clear fd";
        /**
         * @var $redis Redis|\Redis
         */
        $redis = $this->container->get('redis');

        $fullFd = $redis->sMembers(RedisConstant::FULL_CONNECT_FD);

        if ($fullFd) {
            $fun = 'send';
            if ($this->server instanceof \Swoole\WebSocket\Server) {
                $fun = 'push';
            }
            foreach ($fullFd as $fd) {
                $isSend = $this->server->{$fun}($fd, json_encode([
                    "listen" => "Heartbeat",
                    "content" => "1",
                ]));
                if (!$isSend) {
                    $redis->sRem(RedisConstant::FULL_CONNECT_FD, $fd);
                    $redis->hDel(RedisConstant::FULL_FD_WORKER, $fd);
                }
            }
        }
    }
}