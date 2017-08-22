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
            // Connect to mysql
            $this->conn = new PDO("mysql:host={$conf['host']};dbname={$conf['dbname']};charset=utf8", $conf['username'], $conf['password']);

            // Set the PDO <span class="error">ERROR</span> mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!$this->conn) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Connect to Database',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (\PDOException $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Database : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Success
        $this->setOutputs([
            'status'   => 'OK',
            'remark'   => '',
            'response' => $this->start_time
        ]);

        return $this;
    }

    public function query($sql = null)
    {
        $this->outputs['service'] .= '<br>Check Query Datas';

        if (!$this->conn) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to Database',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Get SQL
        $sql = (!empty($sql)) ? $sql : 'SELECT CURDATE()';

        // Query
        try {
            $this->conn->query($sql);
        } catch (\PDOException  $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Query Datas : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Success
        $this->setOutputs([
            'status'   => 'OK',
            'remark'   => '',
            'response' => $this->start_time
        ]);

        return $this;
    }
}
