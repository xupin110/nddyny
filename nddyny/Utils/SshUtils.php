<?php
namespace nddyny\Utils;

class SshUtils
{

    private $conn;

    private $shell;

    private $print_callback;

    private $get_content_sleep = 1000 * 1000 * 0.1;

    private $send_length = 1024 * 4;

    private $echo_end_flag = 'echo nddyn\y-093a483c-318b-11e8-8103-535d8e083635-nddyn\y; exit;';

    private $true_end_flag = 'nddyny-093a483c-318b-11e8-8103-535d8e083635-nddyny';

    public function shell($host, $port, $username, $password, $command, $print_callback)
    {
        $this->print_callback = $print_callback;

        try {
            $command = trim($command);
            if(substr($command, -1) != ';') {
                $command .= ';';
            }
            $command .= $this->echo_end_flag;

            $this->print('开始连接服务器' . PHP_EOL);
            if (($this->conn = ssh2_connect($host, $port)) == false) {
                return $this->print('服务器连接失败' . PHP_EOL);
            }
            $this->print('开始验证身份' . PHP_EOL);
            if(empty(trim($password))) {
                ssh2_auth_pubkey_file($this->conn, $username, '~/.ssh/id_rsa.pub', '~/.ssh/id_rsa');
            } elseif (ssh2_auth_password($this->conn, $username, $password) == false) {
                return $this->print('登录失败' . PHP_EOL);
            }
            $this->shell = ssh2_shell($this->conn, null, null, 850);
            $this->command($command);
        }catch (\Exception $e) {
            $this->print($e->getMessage());
        }
        $this->disconnect();
    }

    private function command($command = '')
    {
        fwrite($this->shell, $command . PHP_EOL);
        $out = '';
        $is_end = false;
        $open_sleep = false;
        $i = 0;
        while (true) {
            if($open_sleep) {
                usleep($this->get_content_sleep);
            } else {
                usleep($this->get_content_sleep / 3);
            }
            $out .= stream_get_contents($this->shell);
            if (empty(trim($out)) && !is_numeric($out)) {
                continue;
            }
            if(strpos($out, PHP_EOL) === false && ++ $i % 20 != 0 && strlen($out) < $this->send_length) {
                continue;
            } else {
                $i = 0;
            }
            if(strpos($out, $this->echo_end_flag)) {
                $out = str_replace($this->echo_end_flag, '', $out);
            }
            if(strpos($out, $this->true_end_flag) !== false) {
                $is_end = true;
                $out = str_replace($this->true_end_flag, '', $out);
            }
            if(strlen($out) > $this->send_length) {
                foreach(mb_str_plite($out, $this->send_length) as $value) {
                    $this->print($value);
                    usleep($this->get_content_sleep * 5);
                }
            } else {
                $this->print($out);
            }
            if (!$is_end) {
                $out = '';
                $open_sleep = true;
                continue;
            }
            break;
        }
    }

    private function print($content)
    {
        return ($this->print_callback)($content);
    }

    public function disconnect()
    {
        $this->shell && fclose($this->shell);
        unset($this->conn);
    }
}