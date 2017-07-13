<?php
namespace EggDigital\HealthCheck\Classes;

abstract class Base
{
    private $start_time;
    protected $require_config;
    protected $outputs = [
        'module'   => '',
        'service'  => '',
        'url'      => '',
        'response' => 0,
        'status'   => '<span class="status-success">OK</span>',
        'remark'   => ''
    ];

    public function __construct()
    {
        $this->start_time = microtime(true);
    }

    public function get()
    {
        return $this->outputs;
    }

    protected function validParams($config)
    {
        foreach ($this->require_config as $param_name) {
            // Checkey params is require
            if (!isset($config[$param_name])) {
                return false;
            }
        }
        return true;
    }

    public function __destruct()
    {
        $this->outputs['response'] = microtime(true) - $this->start_time;
    }
}
