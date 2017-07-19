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
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Connect to Socket',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Socket : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Success
        $this->setOutputs([
            'status'   => 'OK',
            'remark'   => '',
            'response' => $this->start_time
        ]);
        
        return $this;
    }
}
