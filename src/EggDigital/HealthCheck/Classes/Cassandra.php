<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use Cassandra\Connection;

class Cassandra extends Base
{
    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Cassandra';
        $this->require_config = ['node'];
    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status']  = '<span class="status-error">ERROR</span>';
            $this->outputs['remark']  = '<span class="status-error">Require parameter (' . implode(',', $this->require_config) . ')</span>';

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
                $this->outputs['status']  = '<span class="status-error">ERROR</span>';
                $this->outputs['remark']  = '<span class="status-error">Can\'t Connect to Database</span>';
            }
        } catch (Exception $e) {
            $this->outputs['status']  = '<span class="status-error">ERROR</span>';
            $this->outputs['remark']  = '<span class="status-error">Can\'t Connect to Database : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function query($cql = null)
    {
        $this->outputs['service'] = 'Check Query Datas';

        if (!$this->conn) {
            $this->outputs['status']  = '<span class="status-error">ERROR</span>';
            $this->outputs['remark']  = '<span class="status-error">Can\'t Connect to Database</span>';

            return $this;
        }

        // Defualt CQL
        if (empty($cql)) {
            $cql = "SELECT count(*) FROM noti_request WHERE app_id = 14 ALLOW FILTERING";
        }

        try {
            // Query
            $statement = $cassandra->queryAsync($cql);

            // Wait until received the response, can be reversed order
            $result = $statement->getResponse();
            $result = $result->fetchRow()['count'];
            if (!$result) {
                $this->outputs['status']  = '<span class="status-error">ERROR</span>';
                $this->outputs['remark']  = '<span class="status-error">Can\'t Query Datas</span>';
            }
        } catch (Exception  $e) {
            $this->outputs['status']  = '<span class="status-error">ERROR</span>';
            $this->outputs['remark']  = '<span class="status-error">Can\'t Query Datas : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
