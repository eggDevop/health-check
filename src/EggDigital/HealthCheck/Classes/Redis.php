<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use Predis;

class Redis extends Base
{
    private $redis;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Redis';
        $this->require_config = ['host'];
    }

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

        try {
            // Connect to redis
            $this->redis = new Predis\Client([
                'scheme' => (isset($conf['scheme'])) ? $conf['scheme'] : 'tcp',
                'host'   => $conf['host'],
                'port'   => (isset($conf['port'])) ? $conf['port'] : 6379
            ]);
        
            if (!$this->redis) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Connect to Redis',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Redis : ' . $e->getMessage(),
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

    public function totalQueue($keys, $max_job = null)
    {
        if (!$this->redis) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Redis',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Get queue
        $total = 0;
        foreach ($keys as $key) {
            $total += (int)$this->redis->llen("resque:queue:{$key}");
        }

        // Check Max Queue
        if (!empty($max_job) && $total > $max_job) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => "Queues > {$max_job}",
                'response' => $this->start_time
            ]);
            
            return $this;
        }

        // Success
        $this->setOutputs([
            'service'         => "Number of Queue : {$total}",
            'status'   => 'OK',
            'remark'   => '',
            'response' => $this->start_time
        ]);

        return $this;
    }
}
