<?php

namespace app\Models\nddyny\Table;

class UserTable extends Table
{

    const TABLE_NAME = 'nddyny_user';

    public function info($account, $status = STATUS_ALL)
    {
        $mail = $account;
        $sql = $this->mysql_pool->dbQueryBuilder->from(self::TABLE_NAME)->where('status', $status)
            ->where('mail', $mail)
            ->select('user_id, nickname, truename, mail, google_auth_secret, password, password_salt, role');
        return $this->find($sql);
    }

    public function resetPassword($account, $password, $status = STATUS_ALL)
    {
        $mail = $account;
        $sql = $this->mysql_pool->dbQueryBuilder->update(self::TABLE_NAME)->where('status', $status)
            ->where('mail', $mail)
            ->set('password', $password);
        return $this->update($sql);
    }
}