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
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Require parameter (' . implode(',', $this->require_config) . ')';

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $time_out = (isset($conf['timeout'])) ? $conf['timeout'] : 500;
            $this->gm_admin = new GearmanAdmin($conf['host'], $conf['port'], $time_out);
        } catch (Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to Gearman : ' . $e->getMessage();
            return $this;
        }

        // Get gaerman status
        if (false === $status = (array)$this->gm_admin->getStatus()) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to Gearman';
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
        $this->outputs['status'] = '';
        $this->outputs['remark'] = '';

        if (!$this->gm_admin) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to Gearman';

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['0'])) {
            $this->outputs['status'] .= '<br>ERROR';
            $this->outputs['remark'] .= "<br>Dose not exist queues name > {$queue_name}";

            return $this;
        }

        $this->outputs['status']  .= '<br>OK';
        $this->outputs['service'] .= "<br>Number of Queue {$queue_name} : {$this->gm_status[$queue_name]['0']}";
        
        // Check Max Queue
        if (!isset($max_job) && $this->gm_status[$queue_name]['0'] > $max_job) {
            $this->outputs['status'] .= '<br>ERROR';
            $this->outputs['remark'] .= "<br>Queues > {$max_job}";
        }

        return $this;
    }

    // This method want to get amount worker
    public function workerRunning($queue_name)
    {
        if (!$this->gm_admin) {
            $this->outputs['status'] .= '<br>ERROR';
            $this->outputs['remark'] .= '<br>Can\'t Connect to Gearman';

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['1'])) {
            $this->outputs['status'] .= '<br>ERROR';
            $this->outputs['remark'] .= "<br>Dose not exist queues name > {$queue_name}";

            return $this;
        }

        $this->outputs['status']  .= '<br>OK';
        $this->outputs['service'] .= "<br>Number of Worker Running {$queue_name} : {$this->gm_status[$queue_name]['1']}";

        return $this;
    }

    public function workerOnQueue($queue_name)
    {
        if (!$this->gm_admin) {
            $this->outputs['status'] .= '<br>ERROR';
            $this->outputs['remark'] .= '<br>Can\'t Connect to Gearman';

            return $this;
        }

        if (!isset($this->gm_status[$queue_name]['2'])) {
            $this->outputs['status'] .= '<br>ERROR';
            $this->outputs['remark'] .= "<br>Dose not exist queues name > {$queue_name}";

            return $this;
        }

        $this->outputs['status']  .= '<br>OK';
        $this->outputs['service'] .= "<br>Total Worker on Queue {$queue_name} : {$this->gm_status[$queue_name]['2']}";

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
