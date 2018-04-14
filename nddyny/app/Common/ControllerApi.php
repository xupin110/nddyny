<?php
namespace app\Controllers\nddyny\Common;

use ResultObject;

class ControllerApi extends Controller
{

    protected function initialization($controller_name, $method_name)
    {
        parent::initialization($controller_name, $method_name);
        $this->auth_token = $this->http_input->cookie('auth_token');
    }

    protected function render(ResultObject $Result)
    {
        $this->http_output->setHeader('Content-Type', 'text/html; charset=UTF-8');
        return $this->http_output->end($Result->json());
    }

    protected function get()
    {
        return $this->request->get ?? [];
    }

    protected function post()
    {
        return $this->request->post ?? [];
    }

    protected function isPost()
    {
        return $this->http_input->getRequestMethod() == 'POST' ? true : false;
    }

    protected function isGet()
    {
        return $this->http_input->getRequestMethod() == 'GET' ? true : false;
    }

    protected function obStart()
    {
        ob_start();
    }

    protected function obEnd()
    {
        $content = ob_get_contents();
        ob_end_clean();
        $this->http_output->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->http_output->end($content);
    }
}