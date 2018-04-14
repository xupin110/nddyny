<?php
namespace app\Controllers\nddyny\ControllersApiBackend;

use R;
use app\Controllers\nddyny\Common\ControllerApiBackend;

class AppController extends ControllerApiBackend
{

    private $primary_key = 'app_id';

    private $table_name = 'nddyny_app';

    private $fields = 'app_id, app_name, status';

    public function cacheList()
    {
        $this->isLogin();
        return $this->render(R::success($this->loader->model('nddyny\Table\AppTable', $this)->getList(), null, config_get('swoole.ip')));
    }

    public function list()
    {
        $this->isLogin();
        $post = $this->post();
        $draw = param_uint($post, 'draw', true);
        $like_wheres = param_value($post, 'wheres', false, []);
        $orders = param_value($post, 'orders', false, []);
        $status = param_string($post, 'status', false, STATUS_ALL);
        $offset = param_uint($post, 'offset', false, 0);
        $limit = param_uint($post, 'limit', false, 30);
        $result = $this->loader->model('nddyny\Table\Table', $this)->executeSelect(
            $this->table_name, $this->fields, $offset, $limit, $like_wheres, $status, $orders
        );
        return $this->render(R::success($result + [
            'draw' => $draw
        ]));
    }

    public function create()
    {
        $this->isLogin();
        $post = $this->post();
        $data = param_value($post, 'data', true);

        $result = $this->loader->model('nddyny\Table\Table', $this)->executeCreate($this->table_name, $data);
        $this->loader->model('nddyny\Table\AppTable', $this)->setList();
        return $this->render(R::success($result));
    }

    public function update()
    {
        $this->isLogin();
        $post = $this->post();
        $data = param_value($post, 'data', true);

        $result = $this->loader->model('nddyny\Table\Table', $this)->executeUpdate($this->table_name, $this->primary_key, $data);
        $this->loader->model('nddyny\Table\AppTable', $this)->setList();
        return $this->render(R::success($result));
    }

    public function delete()
    {
        $this->isLogin();
        $post = $this->post();
        $data = param_value($post, 'data', true);

        $result = $this->loader->model('nddyny\Table\Table', $this)->executeDelete($this->table_name, $this->primary_key, $data);
        $this->loader->model('nddyny\Table\AppTable', $this)->setList();
        return $this->render(R::success($result));
    }
}