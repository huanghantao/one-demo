<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/12
 * Time: 10:44
 */

namespace App\Test\MixPro;


use One\Http\Controller;

class HttpController extends Controller
{

    use Funs;

    /**
     * 首页
     */
    public function index()
    {
        $code = sha1(uuid());
        $this->session()->set('code', $code);
        return $this->display('index', ['code' => $code]);
    }

    /**
     * ws页面
     */
    public function ws()
    {
        $name = $this->session()->get('name');
        if (!$name) {
            $name = uuid();
            $this->session()->set('name', $name);
        }
        $this->server->set("http.{$name}", 1, time() + 600);
        return $this->display('ws');
    }

    /**
     * http 页面
     */
    public function http()
    {
        $name = $this->session()->get('name');
        if (!$name) {
            $name = uuid();
            $this->session()->set('name', $name);
        }
        $this->server->set("http.{$name}", 1, time() + 600);
        $this->sendTo('all',json_encode(['v' => 1,'n' => $name])."\n");
        return $this->display('http', ['list' => $this->getAllName(), 'name' => $name]);
    }

    /**
     * http轮训
     */
    public function httpLoop()
    {
        $name = $this->session()->get('name');
        $this->server->set("http.{$name}", 1, time() + 600);//续命
        $i = 0;
        do {
            $data = $this->server->getAndDel("data.{$name}");
            $i++;
            \co::sleep(0.1);
        } while ($data === null && $i < 300);
        if ($data) {
            foreach ($data as &$v) {
                $v = json_decode($v, true);
            }
        } else {
            $data = [];
        }
        return $this->json($data);
    }

    /**
     * http发送消息
     */
    public function httpSend()
    {
        $n = $this->request->post('n');
        $d = $this->request->post('d');
        if ($n && $d) {
            $this->sendTo($n, json_encode(['v' => 3, 'n' => $d]) . "\n");
            return '1';
        }
        return '0';
    }

    public function __call($name, $arguments)
    {
        return $this->server->$name(...$arguments);
    }

}