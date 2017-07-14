<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

class RabbitMQ extends Base
{
    private $connection;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'RabbitMQ';
        $this->require_config = ['host', 'port', 'username', 'password'];
    }

    // Method for check connect to rabbitmq
    public function connect($conf)
    {
         $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $this->connection = new AMQPStreamConnection($conf['host'], $conf['port'], $conf['username'], $conf['password']);
            
            // Check status rabbitmq
            if (!$this->connection->isConnected()) {
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to RabbitMQ</span>';

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to RabbitMQ : ' . $e->getMessage() . '</span>';

            return $this;
        }

        // Success
        $this->outputs['status'] .= '<br>OK';
        $this->outputs['remark'] .= '<br>';

        return $this;
    }

    // Method for get total queue in rabbitmq
    public function totalQueue($queue_name, $max_job = null)
    {
        if (!$this->connection) {
            $this->outputs['service'] .= "<br><span class=\"error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="error">Can\'t Connect to RabbitMQ</span>';

            return $this;
        }

        try {
            $this->channel = $this->connection->channel();

            list(,$msg_count,) = $this->channel->queue_declare($queue_name, true, false, false, false);

        } catch (AMQPProtocolChannelException $e) {
            $this->outputs['service'] .= "<br><span class=\"error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="error">Can\'t Get Queue Name : ' . $e->getMessage() . '</span>';

            // Re connect channel
            $this->channel = $this->connection->channel();

            return $this;
        }

        // Check Max Queue
        if (!isset($max_job) && $msg_count > $max_job) {
            $this->outputs['service'] .= "<br><span class=\"error\">Number of Queue <b>{$queue_name}</b> : {$msg_count}</span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= "<br><span class=\"error\">Queues > {$max_job}</span>";
            
            return $this;
        }

        // Success
        $this->outputs['service'] .= "<br>Number of Queue <b>{$queue_name}</b> : {$msg_count}";
        $this->outputs['status']  .= '<br>OK';
        $this->outputs['remark']  .= !empty($max_job) ? "<br>Queues > {$max_job} alert" : '<br>';

        return $this;
    }

    // This method want to get amount worker
    public function workerOnQueue($queue_name)
    {
        if (!$this->connection) {
            $this->outputs['service'] .= "<br><span class=\"error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="error">Can\'t Connect to RabbitMQ</span>';

            return $this;
        }

        try {
            $this->channel = $this->connection->channel();

            list(,,$consumer_count) = $this->channel->queue_declare($queue_name, true, false, false, false);

        } catch (AMQPProtocolChannelException $e) {
            $this->outputs['service'] .= "<br><span class=\"error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="error">Can\'t Get Worker : ' . $e->getMessage() . '</span>';

            // Re connect channel
            $this->channel = $this->connection->channel();

            return $this;
        }

        // Success
        $this->outputs['service'] .= "<br>Total Worker on Queue <b>{$queue_name}</b> : {$consumer_count}";
        $this->outputs['status']  .= '<br>OK';
        $this->outputs['remark']  .= '<br>';

        return $this;
    }

    public function __destruct()
    {
        $this->outputs['response'] += (microtime(true) - $this->start_time);
    }
}
