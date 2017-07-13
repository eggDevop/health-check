<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use Predis;

class Redis extends Base
{
    private $redis;

    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Redis';
        $this->require_config = ['host'];
    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status']  = '<span class="status-error">ERROR</span>';
            $this->outputs['remark']  = 'Require parameter (' . implode(',', $this->require_config) . ')';

            return $this;
        }

        try {
            // Connect to redis
            $this->redis = new Predis\Client([
                'scheme' => (isset($conf['scheme'])) ? $conf['scheme'] : 'tcp',
                'host'   => $conf['host'],
                'port'   => (isset($conf['port'])) ? $conf['port'] : 6379
            ]);
        
            if (!$redis) {
                $this->outputs['status']  = '<span class="status-error">ERROR</span>';
                $this->outputs['remark']  = 'Can\'t Connect to Redis';
            }
        } catch (Exception $e) {
            $this->outputs['status']  = '<span class="status-error">ERROR</span>';
            $this->outputs['remark']  = 'Can\'t Connect to Redis : ' . $e->getMessage();
        }

        return $this;
    }

    public function totalQueue($keys, $max_job = null)
    {
        if (!$this->redis) {
            $this->outputs['status']  = '<span class="status-error">ERROR</span>';
            $this->outputs['remark']  = 'Can\'t Connect to Redis';

            return $this;
        }

        // Get queue
        $total = 0;
        foreach ($keys as $key) {
            $total += (int)$this->redis->llen("resque:queue:{$key}");
        }

        $this->outputs['service'] .= "<br>Number of Queue : {$total}";
        
        // Check Max Queue
        if (!empty($max_job) && $total > $max_job) {
            $this->outputs['status'] = '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] = 'Queues > {$max_job}';
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
