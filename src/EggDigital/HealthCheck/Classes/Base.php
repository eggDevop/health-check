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
        'status'   => '',
        'remark'   => ''
    ];

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

    protected function setOutputs($datas = [])
    {
        
        if (isset($datas['RabbitMQ Server'])) {

            $error = false;
            if (array_key_exists('status', $datas['RabbitMQ Server']) && $datas['RabbitMQ Server']['status'] === 'ERROR') {
                $error = true;
            }

            foreach ($datas['RabbitMQ Server'] as $key => $value) {
                if (isset($this->outputs[$key]) && $key !== 'response') {
                    $this->outputs[$key] .= ($error) ? "<br><span class=\"error\">{$value}</span>" : "<br>{$value}";
                } else {
                    $this->outputs['response'] .= $value;
                }
            }

        }else{

            $error = false;
            if (array_key_exists('status', $datas) && $datas['status'] === 'ERROR') {
                $error = true;
            }

            foreach ($datas as $key => $value) {
                if (isset($this->outputs[$key]) && $key !== 'response') {
                    $this->outputs[$key] .= ($error) ? "<br><span class=\"error\">{$value}</span>" : "<br>{$value}";
                } else {
                    $this->outputs['response'] += (microtime(true) - $value);
                }
            }

        }
    }
}
