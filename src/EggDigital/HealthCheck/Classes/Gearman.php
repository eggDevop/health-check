<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use Ibmurai\PhpGearmanAdmin\GearmanAdmin;

class Gearman extends Base
{
    private $gm_admin;

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
            // Check status gearman
            if (!$this->gm_admin->getStatus()) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t Connect to Gearman';
            }
        } catch (Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to Gearman : ' . $e->getMessage();
        }

        return $this;
    }

    // Method for get total queue in german server
    public function totalQueue($queue_name, $max_job = [])
    {
        $this->outputs['status'] = '';
        $this->outputs['remark'] = '';

        if (!$this->gm_admin) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Connect to Gearman';

            return $this;
        }

        // Get queue
        $res = (array)$this->gm_admin->getStatus();

        $status = $this->getNumberOfQueueFromExecuteOutput($res);

        foreach ($queue_name as $q) {
            if (!isset($status[$q]['msg_count'])) {
                continue;
            }

            $this->outputs['status']  .= '<br>OK';
            $this->outputs['service'] .= "<br>Number of Queue {$q} : {$status[$q]['msg_count']}";
            
            // Check Max Queue
            if (!isset($max_job[$q]) && $status[$q]['msg_count'] > $max_job[$q]) {
                $this->outputs['status'] .= '<br>ERROR';
                $this->outputs['remark'] .= "<br>Queues > {$max_job[$q]}";
            }
        }

        return $this;
    }

    // This method want to get amount worker
    // But response $this->gm_admin->getWorkers() is Bug! can't get array[0]
    public function workerRunning($queue_name)
    {
        if (!$this->gm_admin) {
            $this->outputs['status'] = 'ERROR';
            $this->outputs['remark'] = 'Can\'t Connect to Gearman';

            return $this;
        }

        // Get queue
        $res = (array)$this->gm_admin->getStatus();

        $status = getNumberOfQueueFromExecuteOutput($res);

        foreach ($queue_name as $q) {
            if (!isset($status[$q]['workers'])) {
                continue;
            }

            $this->outputs['status']  .= '<br>OK';
            $this->outputs['service'] .= "<br>Number of Worker {$q} : {$status[$q]['workers']}";
        }

        return $this;
    }

    // Method for format execute output
    private function getNumberOfQueueFromExecuteOutput($res)
    {
        // Define output
        $status = [];
        $datas = explode("\n", $datas);
        if (! empty($datas)) {
            foreach ($datas as $data) {
                if (empty($data) || $line === '.') {
                    break;
                }

                // Get number of queue
                $queues = explode("\t", $data);

                // KEY = Queue name
                $status[$queues['0']]['msg_count'] = $queues['1'];
                $status[$queues['0']]['running']   = $queues['2'];
                $status[$queues['0']]['workers']   = $queues['3'];
            }
        }
        
        return $status;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
