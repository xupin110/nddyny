<?php

namespace app\Models\nddyny\Table;

use R;
use Server\Asyn\Mysql\Miner;
use app\Controllers\nddyny\Common\Model;

class Table extends Model
{

    /**
     * count
     * @param Miner $sql
     * @return \ResultObject
     */
    public function count(Miner $sql)
    {
        return $this->findField($sql->select('count(*) AS count'));
    }

    /**
     * 取单条数据
     * @param Miner $sql
     * @return \ResultObject
     */
    public function find(Miner $sql)
    {
        $data = $this->execute($sql->limit(1));
        return $data['result'][0] ?? [];
    }

    /**
     * 取单条数据的单个值
     * @param Miner $sql
     * @return \ResultObject
     */
    public function findField($sql)
    {
        $info = $this->find($sql);
        return current($info);
    }

    /**
     * 取多条数据
     * @param Miner $sql
     * @return \ResultObject
     */
    public function list($sql)
    {
        $data = $this->execute($sql);
        return $data['result'];
    }

    /**
     * 取多条数据的单个值
     * @param Miner $sql
     * @return \ResultObject
     */
    public function listField($sql)
    {
        $list = $this->list($sql);
        $result = [];
        foreach ($list as $info) {
            $result[] = current($info);
        }
        return $result;
    }

    /**
     * 添加
     * @param Miner $sql
     * @return \ResultObject
     */
    public function insert($sql)
    {
        $person = 0;
        $sql->set('created_user', $person);
        $sql->set('updated_user', $person);
        $data = $this->execute($sql);
        return $data['insert_id'];
    }

    /**
     * 修改
     * @param Miner $sql
     * @return \ResultObject
     */
    public function update($sql)
    {
        $person = 0;
        $sql->set('updated_user', $person);
        $data = $this->execute($sql);
        return $data['affected_rows'];
    }

    /**
     * 删除
     * @param Miner $sql
     * @return \ResultObject
     */
    public function delete($sql)
    {
        $data = $this->execute($sql);
        return $data['affected_rows'];
    }

    /**
     * 执行sql语句，处理错误信息
     * @param Miner $sql
     * @return \ResultObject
     * @throws \NddynyException
     */
    public function execute(Miner $sql)
    {
        try {
            return $sql->query();
        } catch (\Exception $e) {
            $Result = R::error($e->getMessage(), $e->getCode(), $e->getPrevious());
            log_error(LOG_MYSQL, $Result);
            // $Result = R::error(null, R::MYSQL_CRUD_ERROR);
            throw new \NddynyException($Result);
        }
    }

    public function executeSelect($table_name, $fields = '*', $offset = 0, $limit = 30, $like_wheres = [], $status = STATUS_ALL, $orders = [])
    {
        if (isset($like_wheres['status'])) {
            $status = $like_wheres['status'];
            unset($like_wheres['status']);
        }
        if (is_array($status) && count($status) == 1) {
            $status = $status[0];
        }

        $sql = $this->mysql_pool->dbQueryBuilder->from($table_name);
        if (is_array($status)) {
            if (array_search('all', $status)) {
                $sql->where('status', STATUS_DELETE, $sql::NOT_EQUALS);
            } else {
                $sql->whereIn('status', $status);
            }
        } else {
            if ($status == STATUS_ALL) {
                $sql->where('status', STATUS_DELETE, $sql::NOT_EQUALS);
            } else {
                $sql->where('status', $status);
            }
        }
        foreach ($like_wheres as $key => $value) {
            if (is_array($value) && count($value) > 1) {
                if (isset($value['value']) && $value['type'] == 'notIn') {
                    $sql->whereNotIn($key, $value['value']);
                } else {
                    $sql->whereIn($key, $value);
                }
                continue;
            } elseif (is_array($value)) {
                $value = $value[0];
            }
            $sql->where($key, $value, $sql::LIKE);
        }
        $count_sql = clone $sql;
        $total = $this->count($count_sql);
        foreach ($orders as $key => $order) {
            $sql->orderBy($key, $order);
        }
        $sql->limit($limit, $offset)->select(is_array($fields) ? implode(',', $fields) : $fields);
        $list = $this->list($sql);
        return [
            'total' => $total,
            'list' => $list
        ];
    }

    public function executeCreate($table_name, $data)
    {
        $sql = $this->mysql_pool->dbQueryBuilder->insert($table_name);
        foreach ($data as $field => $value) {
            $sql->set($field, $value);
        }
        return $this->insert($sql);
    }

    public function executeUpdate($table_name, $primary_key, $data)
    {
        $sql = $this->mysql_pool->dbQueryBuilder->update($table_name);
        $sql->where($primary_key, $data[$primary_key]);
        unset($data[$primary_key]);
        foreach ($data as $field => $value) {
            $sql->set($field, $value);
        }
        return $this->update($sql);
    }

    public function executeDelete($table_name, $primary_key, $data)
    {
        $sql = $this->mysql_pool->dbQueryBuilder->delete('FROM ' . $table_name);
        $sql->where($primary_key, $data[$primary_key]);
        return $this->delete($sql);
    }
}