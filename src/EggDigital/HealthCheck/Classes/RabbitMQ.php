<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use PhpAmqpLib\Connection\AMQPStreamConnection;

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
            if ( !$this->connection->isConnected() ) {
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
    public function totalQueue($queue_name)
    {
        if (!$this->connection) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to RabbitMQ';

            return $this;
        }

        $this->channel = $this->connection->channel();

        foreach ($queue_name as $val) {
            list(,$messageCount,) = $this->channel->queue_declare($val, false, false, false, false);

            $this->outputs['service'] .= "<br>Number of Queue {$val} : {$messageCount}";
        }

        return $this;
    }

    // This method want to get amount worker
    public function workerRunning($queue_name)
    {
        if (!$this->connection) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to RabbitMQ';

            return $this;
        }

        $this->channel = $this->connection->channel();

        foreach ($queue_name as $val) {
            list(,,$consumerCount) = $this->channel->queue_declare($val, false, false, false, false);

            $this->outputs['service'] .= "<br>Number of Worker {$val} : {$consumerCount}";
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
