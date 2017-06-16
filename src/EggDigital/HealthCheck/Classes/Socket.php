<?php
namespace EggDigital\HealthCheck\Classes;

class Socket extends Base
{
    public function connect($server, $port)
    {
        $remote_socket = ($server === null) ? 'unix://'.$server : 'tcp://'.$server.':'.$port;
        $errno         = '';
        $errstr        = '';
        $flags         = STREAM_CLIENT_CONNECT;
        $flags         = $flags | STREAM_CLIENT_PERSISTENT;
        $socket        = @stream_socket_client($remote_socket, $errno, $errstr, 2.5, $flags);

        return $socket;
    }
}
