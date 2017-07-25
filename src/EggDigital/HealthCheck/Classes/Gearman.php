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
            $time_out = (isset($conf['timeout'])) ? $conf['timeout'] : 500;
            $this->gm_admin = new GearmanAdmin($conf['host'], $conf['port'], $time_out);
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Gearman : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Get gaerman status
        if (false === $status = (array)$this->gm_admin->getStatus()) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Gearman',
                'response' => $this->start_time
            ]);

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
    public function queue($queue_name, $max_job = null, $check_worker = false)
    {
        if (!$this->gm_admin) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Gearman',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Job on queue
        if (!isset($this->gm_status[$queue_name]['0'])) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Does not exits queue name',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Worker Running
        if (!isset($this->gm_status[$queue_name]['1'])) {
            $this->setOutputs([
                'service'  => "Number of Worker Running on Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Get Worker Runing',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Worker Total
        if (!isset($this->gm_status[$queue_name]['2'])) {
            $this->setOutputs([
                'service'  => "Total Worker on Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Get Worker Total',
                'response' => $this->start_time
            ]);

            return $this;
        }
        
        // Check Max Queue
        if (isset($max_job) && $this->gm_status[$queue_name]['0'] > $max_job) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b>: {$this->gm_status[$queue_name]['0']}",
                'url'      => "Number of Worker (Running: {$this->gm_status[$queue_name]['1']} , Total: {$this->gm_status[$queue_name]['2']})",
                'status'   => 'ERROR',
                'remark'   => "Queues > {$max_job}</span>",
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Success
        $this->setOutputs([
            'service'  => "Number of Queue <b>{$queue_name}</b> : {$this->gm_status[$queue_name]['0']}",
            'status'   => 'OK',
            'url'      => "Number of Worker (Running: {$this->gm_status[$queue_name]['1']} , Total: {$this->gm_status[$queue_name]['2']})",
            'remark'   => !empty($max_job) ? "Queues > {$max_job} alert" : '',
            'response' => $this->start_time
        ]);

        return $this;
    }
}
