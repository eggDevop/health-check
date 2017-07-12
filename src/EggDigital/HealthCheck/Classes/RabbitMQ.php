<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

class RabbitMQ extends Base
{
    private $connection;

    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'RabbitMQ';
        $this->require_config = ['host', 'port', 'username', 'password'];
    }

    // Method for check connect to rabbitmq
    public function connect($conf)
    {
         $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Require parameter (' . implode(',', $this->require_config) . ')';

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $this->connection = new AMQPStreamConnection($conf['host'], $conf['port'], $conf['username'], $conf['password']);
            
            // Check status rabbitmq
            if (!$this->connection->isConnected()) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t Connect to RabbitMQ';
            }
        } catch (Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to RabbitMQ : ' . $e->getMessage();
        }

        return $this;
    }

    // Method for get total queue in rabbitmq
    public function totalQueue($queue_name, $max_job = [])
    {
        $this->outputs['status'] = 'OK';
        $this->outputs['remark'] = '';

        if (!$this->connection) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to RabbitMQ';

            return $this;
        }

        $this->channel = $this->connection->channel();

        foreach ($queue_name as $q) {
            try {
                list(,$msg_count,) = $this->channel->queue_declare($q, true, false, false, false);
            } catch (AMQPProtocolChannelException $e) {
                $this->outputs['status'] .= '<br>ERROR';
                $this->outputs['remark'] .= '<br>Can\'t Get Queue Name : ' . $e->getMessage();

                // Re connect channel
                $this->channel = $this->connection->channel();
                continue;
            }

            $this->outputs['service'] .= "<br>Number of Queue {$q} : {$msg_count}";

            // Check Max Queue
            if (!isset($max_job[$q]) && $status[$q]['msg_count'] > $max_job[$q]) {
                $this->outputs['status'] .= '<br>ERROR';
                $this->outputs['remark'] .= "<br>Queues > {$max_job[$q]}";
            }
        }

        return $this;
    }

    // This method want to get amount worker
    public function workerRunning($queue_name)
    {
        $this->outputs['status']  = '';
        $this->outputs['remark']  = '';

        if (!$this->connection) {
            $this->outputs['status'] = 'ERROR';
            $this->outputs['remark'] = 'Can\'t Connect to RabbitMQ';

            return $this;
        } else {
        }

        $this->channel = $this->connection->channel();

        foreach ($queue_name as $q) {
            try {
                list(,,$consumer_count) = $this->channel->queue_declare($q, true, false, false, false);
            } catch (AMQPProtocolChannelException $e) {
                $this->outputs['status']  .= '<br>ERROR';
                $this->outputs['remark']  .= '<br>Can\'t Get Worker : ' . $e->getMessage();

                // Re connect channel
                $this->channel = $this->connection->channel();
                continue;
            }

            $this->outputs['status']  .= '<br>OK';
            $this->outputs['service'] .= "<br>Number of Worker {$q} : {$consumer_count}";
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
