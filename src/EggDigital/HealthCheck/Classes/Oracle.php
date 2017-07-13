<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Oracle extends Base
{
    private $conn;

    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Oracle';
        $this->require_config = ['host', 'port', 'username', 'password', 'dbname', 'charset'];
    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

            return $this;
        }

        // Set url
        $this->outputs['url'] = "{$conf['host']}:{$conf['port']}";

        try {
            // Connect to oracle
            $this->conn = oci_connect($conf['username'], $conf['password'], "{$conf['host']}:{$conf['port']}/{$conf['dbname']}", $conf['charset']);

            if (!$this->conn) {
                $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
                $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Database</span>';
            }
        } catch (\Exception $e) {
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Database : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function query($sql)
    {
        $this->outputs['service'] = 'Check Query Datas';

        if (!$this->conn) {
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Connect to Database</span>';

            return $this;
        }

        // Query
        try {
            $orc_parse = oci_parse($this->conn, $sql);
            $orc_exec = oci_execute($orc_parse);
            oci_free_statement($orc_parse);

            if (!$orc_exec) {
                $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
                $this->outputs['remark'] .= '<span class="status-error">Can\'t Query Datas</span>';
            }
        } catch (\Exception $e) {
            $this->outputs['status'] .= '<span class="status-error">ERROR</span>';
            $this->outputs['remark'] .= '<span class="status-error">Can\'t Query Datas : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
