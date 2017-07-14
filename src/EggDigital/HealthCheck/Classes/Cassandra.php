<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use Cassandra\Connection;

class Cassandra extends Base
{
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Cassandra';
        $this->require_config = ['node'];
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
        $this->outputs['url'] = $conf['node'];

        try {
            $connection = (isset($conf['node']['keyspace']))
                ? new Connection($conf['node'], $conf['node']['keyspace'])
                : new Connection($conf['node']);

            $this->conn = $connection->connect();
        
            if (!$this->conn) {
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to Database</span>';

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to Database : ' . $e->getMessage() . '</span>';

            return $this;
        }

        // Success
        $this->outputs['status'] .= '<br>OK';
        $this->outputs['remark'] .= '<br>';

        return $this;
    }

    public function query($cql = null)
    {
        $this->outputs['service'] = 'Check Query Datas';

        if (!$this->conn) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Can\'t Connect to Database</span>';

            return $this;
        }

        // Defualt CQL
        $cql = (!empty($cql)) ? $cql : 'SELECT count(*) FROM noti_request WHERE app_id = 14 ALLOW FILTERING';

        try {
            // Query
            $statement = $cassandra->queryAsync($cql);

            // Wait until received the response, can be reversed order
            $result = $statement->getResponse();
            $result = $result->fetchRow()['count'];
            if (!$result) {
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="error">Can\'t Query Datas</span>';

                return $this;
            }
        } catch (Exception  $e) {
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
