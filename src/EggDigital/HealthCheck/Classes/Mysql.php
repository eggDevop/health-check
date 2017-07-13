<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use PDO;

class Mysql extends Base
{
    private $conn;

    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Mysql';
        $this->require_config = ['host', 'username', 'password', 'dbname'];
    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status'] .= '<span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

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
                $this->outputs['status'] .= '<span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Database</span>';
            }
        } catch (PDOException $e) {
            $this->outputs['status'] .= '<span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Database : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function query($sql)
    {
        $this->outputs['service'] = 'Check Query Datas';

        if (!$this->conn) {
            $this->outputs['status'] .= '<span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Database</span>';

            return $this;
        }

        // Query
        try {
            $this->conn->query($sql);
        } catch (PDOException  $e) {
            $this->outputs['status'] .= '<span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Query Datas : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
