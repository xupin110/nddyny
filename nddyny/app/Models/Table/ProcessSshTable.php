<?php

namespace app\Models\nddyny\Table;

class ProcessSshTable extends Table
{

    const TABLE_NAME = 'nddyny_process_ssh';

    public function groupNames($app_id, $status = STATUS_ALL)
    {
        $sql = $this->mysql_pool->dbQueryBuilder->from(self::TABLE_NAME)->where('status', $status)
            ->where('app_id', $app_id)
            ->groupBy('group_name')
            ->select('group_name');
        return $this->listField($sql);
    }
}