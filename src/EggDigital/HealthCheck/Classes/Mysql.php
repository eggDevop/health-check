<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use PDO;

class Mysql extends Base
{
    private $conn;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Mysql';
        $this->require_config = ['host', 'username', 'password', 'dbname'];
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
            // Connect to mysql
            $this->conn = new PDO("mysql:host={$conf['host']};dbname={$conf['dbname']};charset=utf8", $conf['username'], $conf['password']);

            // Set the PDO <span class="error">ERROR</span> mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!$this->conn) {
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Database</span>';
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }
        } catch (PDOException $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Database : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }

    public function query($sql)
    {
        $this->outputs['service'] = 'Check Query Datas';

        if (!$this->conn) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Connect to Database</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Query
        try {
            $this->conn->query($sql);
        } catch (PDOException  $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Query Datas : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }
}
