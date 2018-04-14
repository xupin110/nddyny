<?php
namespace app\Controllers\nddyny\Common;

use R;
use ResultObject;

class ControllerTcpWebsocket extends Controller
{

    protected $uid;

    protected $user_random;

    protected $data;

    protected $attach;

    protected function initialization($controller_name, $method_name)
    {
        parent::initialization($controller_name, $method_name);
        $this->data = json_decode(json_encode($this->client_data->data), true);
        $this->attach = isset($this->client_data->attach) ? json_decode(json_encode($this->client_data->attach), true) : [];
        $this->auth_token = $this->client_data->auth_token ?? null;
        $this->user_random = $this->client_data->user_random ?? null;
        if(isset($this->user_random) && !is_numeric($this->user_random)) {
            throw new \NddynyException(R::fail('server.user_random', R::ARGUMENT_INVALID));
        }
    }

    public function destroy()
    {
        $this->data = null;
        $this->attach = null;
        parent::destroy();
    }

    protected function render(ResultObject $Result)
    {
        return $this->send($this->getResult($Result));
    }

    protected function renderMessage($uid, ResultObject $Result)
    {
        return $this->sendToUid($uid, $this->getResult($Result));
    }

    protected function getResult(ResultObject $Result)
    {
        $Result->attach = $this->attach;
        return $Result->result();
    }

    protected function isLogin()
    {
        $user_info = parent::isLogin();
        $this->uid = $this->user_random ? $this->user_id . '_' . $this->user_random : $this->user_id;
        return $user_info;
    }
}