<?php
namespace EggDigital\HealthCheck\Classes;

use Ibmurai\PhpGearmanAdmin\GearmanAdmin;

class Gearman extends Base
{
    private $gm_admin;

    public function __construct()
    {
        parent::__construct();
        
        $this->outputs['module'] = 'Gearman';
        $this->conf = ['host', 'port', 'timeout'];
    }

    public function connect($conf)
    {
         $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs = [
                'status' => 'ERROR',
                'remark' => 'Require parameter (' . implode(',', $this->conf) . ')'
            ];

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $this->gm_admin = new GearmanAdmin($conf['host'], $conf['port'], $conf['timeout']);
            
            // Check status gearman
            if (!$this->gm_admin->getStatus) {
                $this->outputs = [
                    'status'  => 'ERROR',
                    'remark'  => 'Can\'t connect to gearman'
                ];
            }
        } catch (Exception $e) {
            $this->outputs = [
                'status'  => 'ERROR',
                'remark'  => 'Can\'t connect to gearman : ' . $e->getMessage()
            ];
        }

        return $this;
    }

    // This method want to get amount worker
    // But response $this->gm_admin->getWorkers() is Bug! can't get array[0]
    public function workerRunning()
    {
        if (!$this->gm_admin) {
            $this->outputs = [
                'status'  => 'ERROR',
                'remark'  => 'Can\'t connect to database'
            ];

            return $this;
        }

        $workers = (array)$this->gm_admin->getWorkers();
        $wk = [];
        foreach ($workers as $key => $value) {
            if (strpos($key, 'GearmanAdminWorkers') !== false && strpos($key, '_workers') !== false) {
                $wk = $value;
                break;
            }
        }

        $this->outputs['service'] .= '<br>Number of Worker : ' . count($wk);
        
        return $this;
    }

    // Method for get total queue in german server
    public function totalQueue()
    {
        if (!$this->gm_admin) {
            $this->outputs = [
                'status'  => 'ERROR',
                'remark'  => 'Can\'t connect to database'
            ];

            return $this;
        }

        // Get queue
        $res = (array)$gm_admin->getStatus();
        // $res = shell_exec( "(echo status ; sleep 0.1) | netcat {$server} {$port}" );
        
        $total = $this->getNumberOfQueueFromStatusOutput($res);
        
        $this->outputs['service'] .= '<br>Number of Queue : ' . $total;

        return $this;
    }

    // Method for format execute output
    // private function getNumberOfQueueFromExecuteOutput($res)
    // {
    //     // Define output
    //     $total = 0;
    //     $datas = explode("\n", $res);
    //     if (! empty($datas)) {
    //         foreach ($datas as $data) {
    //             if (!empty($data) || $data !== '.') {
    //                 // Get number of queue
    //                 $queues = explode("\t", $data);
    //                 $total += ((isset($queues['1']) && ! empty($queues['1'])) ? (int)$queues['1'] : 0);
    //             }
    //         }
    //     }
        
    //     return $total;
    // }
    
    // Method for format admin status output
    private function getNumberOfQueueFromStatusOutput($datas)
    {
        // Define output
        $total = 0;
        foreach ($datas as $queues) {
            // Loop for queue
            foreach ($queues as $queue) {
                if (isset($queue['0'])) {
                    $total += (int)$queue['0'];
                }
            }
        }
        
        return $total;
    }
}
