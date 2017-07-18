<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Socket extends Base
{
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Socket';
        $this->require_config = ['host', 'port'];
    }

    public function connect($conf)
    {
        try {
            $remote_socket = ($server === null) ? "unix://{$conf['host']}" : "tcp://{$conf['host']}:{$conf['port']}";
            $err_no        = '';
            $err_str       = '';
            $flags         = STREAM_CLIENT_CONNECT;
            $flags         = $flags | STREAM_CLIENT_PERSISTENT;
            $socket        = @stream_socket_client($remote_socket, $err_no, $err_str, 2.5, $flags);

            if (!$socket) {
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Socket</span>';
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Socket : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);
        
        return $this;
    }
}
