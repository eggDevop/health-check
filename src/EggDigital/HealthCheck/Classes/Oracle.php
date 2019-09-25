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

    public function oracleconnect($conf)
    {
        $this->outputs['service'] = 'Check Connection';
        foreach ($conf['pathfileshealthcheck'] as $key => $value) {
            $jsondata = json_decode(file_get_contents($value));
            $currentDate = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")));
            $periodcurrentDate = date("Y-m-d H:i:s", strtotime('-3 minutes'));
            $flagDate = date("Y-m-d H:i:s", strtotime($jsondata->status->datetime));
 
            if (isset($jsondata)) {
                if (($flagDate >= $periodcurrentDate) && ($flagDate <= $currentDate)){
                    foreach ($jsondata->status->data as $k => $value) {
                        $service .= $key . ' ➡ Oracle DB' . '<br>';
                        if ($value == "ok") { 
                            $url .= $k . '<br>';
                            $status .= '<br>'. 'OK';
                            $remark .= '<br>';
                        }else{
                            $url .= $k . '<br>';
                            $status .= '<br><span class="error">ERROR</span>';
                            $remark .= '<br><span class="error">'. $key . ' ➡ Oracle DB can not connect.</span>';
                        }
                    }
                }else{
                    $service .= $key . ' ➡ Oracle DB' . '<br>';
                    $url .= $k . '<br>';
                    $status .= '<br><span class="error">ERROR</span>';
                    $remark .= '<br><span class="error">Health check not update.</span>';   
                }
            }else{
                $service .= $key . ' ➡ Oracle DB' . '<br>';
                $url .= $k . '<br>';
                $status .= '<br><span class="error">ERROR</span>';
                $remark .= '<br><span class="error">Health check files status not found.</span>';
            }
        }

        $this->setOutputs([
            'RabbitMQ Server' => [
                'service' => $service,
                'url' => $url,
                'status' => $status,
                'remark' => $remark
            ]
        ]);

        return $this;
    }
}
