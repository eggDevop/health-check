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
            $this->outputs['status'] .= '<br><span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="status-error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $this->connection = new AMQPStreamConnection($conf['host'], $conf['port'], $conf['username'], $conf['password']);
            
            // Check status rabbitmq
            if (!$this->connection->isConnected()) {
                $this->outputs['status'] .= '<br><span class="status-error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="status-error">Can\'t Connect to RabbitMQ</span>';
            }
        } catch (Exception $e) {
            $this->outputs['status'] .= '<br><span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="status-error">Can\'t Connect to RabbitMQ : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    // Method for get total queue in rabbitmq
    public function totalQueue($queue_name, $max_job = null)
    {
        if (!$this->connection) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="status-error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Connect to RabbitMQ</span>';

            return $this;
        }

        $this->channel = $this->connection->channel();

        try {
            list(,$msg_count,) = $this->channel->queue_declare($queue_name, true, false, false, false);
        } catch (AMQPProtocolChannelException $e) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="status-error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Get Queue Name : ' . $e->getMessage() . '</span>';

            // Re connect channel
            $this->channel = $this->connection->channel();

            return $this;
        }

        // Check Max Queue
        if (!isset($max_job) && $msg_count > $max_job) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Queue <b>{$queue_name}</b> : {$msg_count}</span>";
            $this->outputs['status']  .= '<br><span class="status-error">ERROR</span>';
            $this->outputs['remark']  .= "<br><span class=\"status-error\">Queues > {$max_job}</span>";
            
            return $this;
        }

        $this->outputs['service'] .= "<br>Number of Queue <b>{$queue_name}</b> : {$msg_count}";
        $this->outputs['status']  .= '<br>OK';

        return $this;
    }

    // This method want to get amount worker
    public function workerOnQueue($queue_name)
    {
        if (!$this->connection) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="status-error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Connect to RabbitMQ</span>';

            return $this;
        }

        $this->channel = $this->connection->channel();

        try {
            list(,,$consumer_count) = $this->channel->queue_declare($queue_name, true, false, false, false);
        } catch (AMQPProtocolChannelException $e) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="status-error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Get Worker : ' . $e->getMessage() . '</span>';

            // Re connect channel
            $this->channel = $this->connection->channel();

            return $this;
        }

        $this->outputs['service'] .= "<br>Total Worker on Queue <b>{$queue_name}</b> : {$consumer_count}";
        $this->outputs['status']  .= '<br>OK';

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
