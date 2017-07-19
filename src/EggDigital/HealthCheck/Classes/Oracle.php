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
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Require parameter (' . implode(',', $this->require_config) . ')',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Set url
        $this->outputs['url'] = "{$conf['host']}:{$conf['port']}";

        try {
            // Connect to oracle
            $this->conn = oci_connect($conf['username'], $conf['password'], "{$conf['host']}:{$conf['port']}/{$conf['dbname']}", $conf['charset']);

            if (!$this->conn) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Connect to Database',
                    'response' => $this->start_time
                ]);
                
                return $this;
            }
        } catch (Exception $e) {
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
        $sql = (!empty($sql)) ? $sql : 'SELECT TO_CHAR(SYSDATE, \'MM-DD-YYYY HH24:MI:SS\') "NOW" FROM DUAL';

        // Query
        try {
            $orc_parse = oci_parse($this->conn, $sql);
            $orc_exec = oci_execute($orc_parse);
            oci_free_statement($orc_parse);

            if (!$orc_exec) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Query Datas',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (Exception $e) {
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
