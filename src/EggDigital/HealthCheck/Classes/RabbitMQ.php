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
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Require parameter (' . implode(',', $this->require_config) . ')',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $this->connection = new AMQPStreamConnection($conf['host'], $conf['port'], $conf['username'], $conf['password']);
            
            // Check status rabbitmq
            if (!$this->connection->isConnected()) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Connect to RabbitMQ',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to RabbitMQ : ' . $e->getMessage(),
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

    // Method for get total queue in rabbitmq
    public function queue($queue_name, $max_job = null, $check_worker = false)
    {
        if (!$this->connection) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to RabbitMQ',
                'response' => $this->start_time
            ]);

            return $this;
        }

        try {
            $this->channel = $this->connection->channel();

            list(,$msg_count, $consumer_count) = $this->channel->queue_declare($queue_name, true, false, false, false);

        } catch (AMQPProtocolChannelException $e) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Get Queue Name : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);

            // Re connect channel
            $this->channel = $this->connection->channel();

            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Check Max Queue
        if (!isset($max_job) && $msg_count > $max_job) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b> : {$msg_count}</b>",
                'url'      => "Worker (Total: {$consumer_count})",
                'status'   => 'ERROR',
                'remark'   => "Queues > {$max_job}",
                'response' => $this->start_time
            ]);
            
            return $this;
        }

        // Success
        $this->setOutputs([
            'service'  => "Number of Queue <b>{$queue_name}</b> : {$msg_count}",
            'url'      => "Worker (Total: {$consumer_count})",
            'status'   => 'OK',
            'remark'   => !empty($max_job) ? "Queues > {$max_job} alert" : '',
            'response' => $this->start_time
        ]);

        return $this;
    }
}
