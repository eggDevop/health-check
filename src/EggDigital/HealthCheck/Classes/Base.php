<?php
namespace EggDigital\HealthCheck\Classes;

abstract class Base
{
    private $start_time;
    protected $conf;
    protected $outputs = [
        'module'   => '',
        'service'  => '',
        'url'      => '',
        'response' => 0.00,
        'status'   => 'OK',
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

    protected function validParams($conf)
    {
        foreach ($this->conf as $k) {
            // Check fix params
            if (!isset($conf[$k])) {
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
