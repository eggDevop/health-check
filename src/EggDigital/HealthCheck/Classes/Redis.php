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
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Require parameter (' . implode(',', $this->require_config) . ')</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

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
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Redis</span>';
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Redis : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }

    public function totalQueue($keys, $max_job = null)
    {
        if (!$this->redis) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Redis</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Get queue
        $total = 0;
        foreach ($keys as $key) {
            $total += (int)$this->redis->llen("resque:queue:{$key}");
        }

        // Check Max Queue
        if (!empty($max_job) && $total > $max_job) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Queues > {$max_job}</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);
            
            return $this;
        }

        // Success
        $this->outputs['service']  .= "<br>Number of Queue : {$total}";
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }
}
