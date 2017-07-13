<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Socket extends Base
{
    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Socket';
        $this->require_config = ['host', 'port'];
    }

    public function connect($conf)
    {
        try {
            $remote_socket = ($server === null) ? "unix://{$conf['host']}" : "tcp://{$conf['host']}:{$conf['port']}";
            $err_no         = '';
            $err_str        = '';
            $flags         = STREAM_CLIENT_CONNECT;
            $flags         = $flags | STREAM_CLIENT_PERSISTENT;
            $socket        = @stream_socket_client($remote_socket, $err_no, $err_str, 2.5, $flags);

            if (!$socket) {
                $this->outputs['status'] .= '<span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Socket</span>';
            }
        } catch (Exception $e) {
            $this->outputs['status'] .= '<span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Socket : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
