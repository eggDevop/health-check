<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use Ibmurai\PhpGearmanAdmin\GearmanAdmin;

class Gearman extends Base
{
    private $gm_admin;
    private $gm_status;

    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Gearman';
        $this->require_config = ['host', 'port','timeout'];
    }

    public function connect($conf)
    {
         $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="status-error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $time_out = (isset($conf['timeout'])) ? $conf['timeout'] : 500;
            $this->gm_admin = new GearmanAdmin($conf['host'], $conf['port'], $time_out);
        } catch (Exception $e) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="status-error">Can\'t Connect to Gearman : ' . $e->getMessage() . '</span>';
            return $this;
        }

        // Get gaerman status
        if (false === $status = (array)$this->gm_admin->getStatus()) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="status-error">Can\'t Connect to Gearman</span>';
            return $this;
        }

        foreach ($status as $queues) {
            $this->gm_status = $queues;
            break;
        }

        return $this;
    }

    // Method for get total queue in german server
    public function totalQueue($queue_name, $max_job = null)
    {
        if (!$this->gm_admin) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Connect to Gearman</span>';

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['0'])) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Does not exits queue name</span>';

            return $this;
        }
        
        // Check Max Queue
        if (!isset($max_job) && $this->gm_status[$queue_name]['0'] > $max_job) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['0']}</span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= "<br><span class=\"status-error\">Queues > {$max_job}</span>";

            return $this;
        }

        $this->outputs['service'] .= "<br>Number of Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['0']}";
        $this->outputs['status']  .= '<br>OK';

        return $this;
    }

    // This method want to get amount worker
    public function workerRunning($queue_name)
    {
        if (!$this->gm_admin) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Connect to Gearman</span>';

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['1'])) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Number of Worker Running on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Get Worker Runing</span>';

            return $this;
        }

        $this->outputs['service'] .= "<br>Number of Worker Running on Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['1']}";
        $this->outputs['status']  .= '<br>OK';

        return $this;
    }

    public function workerOnQueue($queue_name)
    {
        if (!$this->gm_admin) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Connect to Gearman</span>';

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['2'])) {
            $this->outputs['service'] .= "<br><span class=\"status-error\">Total Worker on Queue <b>{$queue_name}</b></span>";
            $this->outputs['status']  .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']  .= '<br><span class="status-error">Can\'t Get Worker</span>';

            return $this;
        }

        $this->outputs['service'] .= "<br>Total Worker on Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['2']}";
        $this->outputs['status']  .= '<br>OK';

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
