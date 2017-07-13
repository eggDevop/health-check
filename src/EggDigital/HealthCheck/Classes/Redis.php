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
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

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
                $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
                $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Redis</span>';
            }
        } catch (Exception $e) {
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Redis : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function totalQueue($keys, $max_job = null)
    {
        if (!$this->redis) {
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Redis</span>';

            return $this;
        }

        // Get queue
        $total = 0;
        foreach ($keys as $key) {
            $total += (int)$this->redis->llen("resque:queue:{$key}");
        }

        // Check Max Queue
        if (!empty($max_job) && $total > $max_job) {
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Queues > {$max_job}</span>';
            
            return $this;
        }

        $this->outputs['service'] .= "<br>Number of Queue : {$total}";

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
