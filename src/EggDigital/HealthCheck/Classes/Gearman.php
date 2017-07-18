<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use Ibmurai\PhpGearmanAdmin\GearmanAdmin;

class Gearman extends Base
{
    private $gm_admin;
    private $gm_status;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Gearman';
        $this->require_config = ['host', 'port','timeout'];
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

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $time_out = (isset($conf['timeout'])) ? $conf['timeout'] : 500;
            $this->gm_admin = new GearmanAdmin($conf['host'], $conf['port'], $time_out);
        } catch (Exception $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Gearman : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Get gaerman status
        if (false === $status = (array)$this->gm_admin->getStatus()) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Gearman</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        foreach ($status as $queues) {
            $this->gm_status = $queues;
            break;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }

    // Method for get total queue in german server
    public function totalQueue($queue_name, $max_job = null)
    {
        if (!$this->gm_admin) {
            $this->outputs['service']  .= "<br><span class=\"error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Gearman</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['0'])) {
            $this->outputs['service']  .= "<br><span class=\"error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Does not exits queue name</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }
        
        // Check Max Queue
        if (!isset($max_job) && $this->gm_status[$queue_name]['0'] > $max_job) {
            $this->outputs['service']  .= "<br><span class=\"error\">Number of Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['0']}</span>";
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= "<br><span class=\"error\">Queues > {$max_job}</span>";
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['service']  .= "<br>Number of Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['0']}";
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= !empty($max_job) ? "<br>Queues > {$max_job} alert" : '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }

    // This method want to get amount worker
    public function workerRunning($queue_name)
    {
        if (!$this->gm_admin) {
            $this->outputs['service']  .= "<br><span class=\"error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Gearman</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['1'])) {
            $this->outputs['service']  .= "<br><span class=\"error\">Number of Worker Running on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Get Worker Runing</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['service']  .= "<br>Number of Worker Running on Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['1']}";
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }

    public function workerOnQueue($queue_name)
    {
        if (!$this->gm_admin) {
            $this->outputs['service']  .= "<br><span class=\"error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Gearman</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['2'])) {
            $this->outputs['service']  .= "<br><span class=\"error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Get Worker</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['service']  .= "<br>Total Worker on Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['2']}";
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }
}
