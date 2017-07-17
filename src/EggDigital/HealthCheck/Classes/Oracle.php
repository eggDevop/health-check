<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Oracle extends Base
{
    private $conn;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Oracle';
        $this->require_config = ['host', 'port', 'username', 'password', 'dbname', 'charset'];
    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

            return $this;
        }

        // Set url
        $this->outputs['url'] = "{$conf['host']}:{$conf['port']}";

        try {
            // Connect to oracle
            $this->conn = oci_connect($conf['username'], $conf['password'], "{$conf['host']}:{$conf['port']}/{$conf['dbname']}", $conf['charset']);

            if (!$this->conn) {
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to Database</span>';
                
                return $this;
            }
        } catch (\Exception $e) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to Database : ' . $e->getMessage() . '</span>';
            
            return $this;
        }

        // Success
        $this->outputs['status'] .= '<br>OK';
        $this->outputs['remark'] .= '<br>';

        return $this;
    }

    public function query($sql = null)
    {
        $this->outputs['service'] .= '<br>Check Query Datas';

        if (!$this->conn) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to Database</span>';

            return $this;
        }

        // Get SQL
        $sql = (!empty($sql)) ? $sql : 'SELECT TO_CHAR(SYSDATE, \'MM-DD-YYYY HH24:MI:SS\') "NOW" FROM DUAL';

        // Query
        try {
            $orc_parse = oci_parse($this->conn, $sql);
            $orc_exec = oci_execute($orc_parse);
            oci_free_statement($orc_parse);

            if (!$orc_exec) {
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="error">Can\'t Query Datas</span>';

                return $this;
            }
        } catch (\Exception $e) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Can\'t Query Datas : ' . $e->getMessage() . '</span>';

            return $this;
        }

        // Success
        $this->outputs['status'] .= '<br>OK';
        $this->outputs['remark'] .= '<br>';
        
        return $this;
    }

    public function __destruct()
    {
        $this->outputs['response'] += (microtime(true) - $this->start_time);
    }
}
