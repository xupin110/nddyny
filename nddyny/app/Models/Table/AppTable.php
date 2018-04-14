<?php
namespace app\Models\nddyny\Table;

class AppTable extends Table
{
    const TABLE_NAME = 'nddyny_app';

    public function getList()
    {
        return json_decode($this->redis_pool->getCoroutine()->get(REDIS_APP_LIST), true);
    }

    public function setList()
    {
        $sql = $this->mysql_pool->dbQueryBuilder->from(self::TABLE_NAME)->where('status', STATUS_ALL)
            ->select('app_id, app_name');
        $list = $this->list($sql);
        $result = [];
        foreach ($list as $info) {
            $result[$info['app_id']] = $info;
        }
        return $this->redis_pool->getCoroutine()->set(REDIS_APP_LIST, json_encode($result));
    }
}