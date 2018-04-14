<?php
namespace app\Models\nddyny\User;

use R;
use app\Controllers\nddyny\Common\Model;

class AuthUser extends Model
{

    const REDIS_USER_AUTH_TOKEN_ = 'user_auth_token_';

    const REDIS_USER_AUTH_FAIL_TIMES_MINUTE_ = 'user_auth_token_fail_times_minute_';

    const REDIS_USER_AUTH_FAIL_TIMES_HOUR_ = 'user_auth_token_fail_times_hour_';

    const REDIS_USER_AUTH_FAIL_TIMES_DAY_ = 'user_auth_token_fail_times_day_';

    protected function failLogin($user_id = null)
    {
        if (isset($user_id)) {
            $this->redis_pool->getCoroutine()->incr($this->getMinuteRedisKey($user_id));
            $this->redis_pool->getCoroutine()->expire($this->getMinuteRedisKey($user_id), 60);
            $this->redis_pool->getCoroutine()->incr($this->getHourRedisKey($user_id));
            $this->redis_pool->getCoroutine()->expire($this->getHourRedisKey($user_id), 3600);
            $this->redis_pool->getCoroutine()->incr($this->getDayRedisKey($user_id));
            $this->redis_pool->getCoroutine()->expire($this->getDayRedisKey($user_id), 3600 * 24);
        }
        return R::fail('帐号或密码错误');
    }

    protected function failTImesVerify($user_id)
    {
        if (10 <= $this->redis_pool->getCoroutine()->get($this->getMinuteRedisKey($user_id))) {
            return R::fail('尝试过于频繁');
        }
        if (30 <= $this->redis_pool->getCoroutine()->get($this->getHourRedisKey($user_id))) {
            return R::fail('尝试过于频繁');
        }
        if (100 <= $this->redis_pool->getCoroutine()->get($this->getDayRedisKey($user_id))) {
            return R::fail('尝试过于频繁');
        }
        return R::success();
    }

    protected function failTImesClear($user_id)
    {
        $this->redis_pool->getCoroutine()->del($this->getMinuteRedisKey($user_id));
        $this->redis_pool->getCoroutine()->del($this->getHourRedisKey($user_id));
        $this->redis_pool->getCoroutine()->del($this->getDayRedisKey($user_id));
    }

    protected function getMinuteRedisKey($user_id)
    {
        return self::REDIS_USER_AUTH_FAIL_TIMES_MINUTE_ . $user_id;
    }

    protected function getHourRedisKey($user_id)
    {
        return self::REDIS_USER_AUTH_FAIL_TIMES_HOUR_ . $user_id;
    }

    protected function getDayRedisKey($user_id)
    {
        return self::REDIS_USER_AUTH_FAIL_TIMES_DAY_ . $user_id;
    }
}