<?php
namespace app\Controllers\nddyny\ControllersApiBackend;

use R;
use app\Controllers\nddyny\Common\ControllerApiBackend;

class TableController extends ControllerApiBackend
{

    public function select()
    {
        $this->isAdmin();
        $post = $this->post();
        $draw = param_uint($post, 'draw', true);
        $table_name = param_string($post, 'table_name', true);
        $fields = param_value($post, 'fields', true);
        $offset = param_uint($post, 'offset', false, 0);
        $limit = param_uint($post, 'limit', false, 30);
        $like_wheres = param_value($post, 'wheres', false, []);
        $status = param_string($post, 'status', false, STATUS_ALL);
        $orders = param_value($post, 'orders', false, []);
        $result = $this->loader->model('nddyny\Table\Table', $this)->executeSelect(
            $table_name, $fields, $offset, $limit, $like_wheres, $status, $orders
        );
        return $this->render(R::success($result + [
            'draw' => $draw
        ]));
    }

    public function create()
    {
        $this->isAdmin();
        $post = $this->post();
        $table_name = param_string($post, 'table_name', true);
        $data = param_value($post, 'data', true);

        $result = $this->loader->model('nddyny\Table\Table', $this)->executeCreate($table_name, $data);
        return $this->render(R::success($result));
    }

    public function update()
    {
        $this->isAdmin();
        $post = $this->post();
        $table_name = param_string($post, 'table_name', true);
        $primary_key = param_string($post, 'primary_key', true);
        $data = param_value($post, 'data', true);

        $result = $this->loader->model('nddyny\Table\Table', $this)->executeUpdate($table_name, $primary_key, $data);
        return $this->render(R::success($result));
    }

    public function delete()
    {
        $this->isAdmin();
        $post = $this->post();
        $table_name = param_string($post, 'table_name', true);
        $primary_key = param_string($post, 'primary_key', true);
        $data = param_value($post, 'data', true);

        $result = $this->loader->model('nddyny\Table\Table', $this)->executeDelete($table_name, $primary_key, $data);
        return $this->render(R::success($result));
    }
}