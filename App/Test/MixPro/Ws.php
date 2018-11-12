<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/12
 * Time: 10:42
 */

namespace App\Test\MixPro;


use One\Swoole\WebSocket;

class Ws extends WebSocket
{
    use Funs;

    private $users = [];

    public function onHandShake(\swoole_http_request $request, \swoole_http_response $response)
    {
        return parent::onHandShake($request, $response);
    }

    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        $data = $frame->data;
        $arr  = json_decode($data, true);
        $n    = $arr['n'];
        $d    = $arr['d'];
        $this->sendTo($n, json_encode(['v' => 3, 'n' => $d]));

    }

    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        $name = $this->session->get('name');
        if ($name) {
            $this->users[$request->fd] = $name;
            $this->sendTo('all', json_encode(['v' => 1, 'n' => $name]));
            $this->bindName($request->fd, $name);
            return true;
        } else {
            return false;
        }
    }

    public function onClose(\swoole_server $server, $fd, $reactor_id)
    {
        parent::onClose($server, $fd, $reactor_id);
        $this->sendTo('all', json_encode(['v' => 2, 'n' => $this->users[$fd]]));
        unset($this->users[$fd]);
    }
}