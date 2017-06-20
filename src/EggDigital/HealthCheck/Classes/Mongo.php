<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Mongo extends Base
{
    private $conn;
    private $conf;
    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Mongo';
        $this->require_config = ['host', 'port', 'dbname'];

    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';
        $this->conf = $conf;
        // Validate parameter
        if (false === $this->validParams($this->conf)) {
            $this->outputs['status'] = 'ERROR';
            $this->outputs['remark'] = 'Require parameter (' . implode(',', $this->require_config) . ')';

            return $this;
        }

        // Set url
        $this->outputs['url'] = $this->conf['host'].':'.$this->conf['port'];

        try{
            if (empty($this->conf['username']) && empty($this->conf['password'])) {
                $this->conn = new \MongoDB\Driver\Manager('mongodb://' . $this->conf['host'] . ':' . $this->conf['port']);
                // $mongodb = (new \MongoDB\Client('mongodb://'.$this->conf['host'].':'.$this->conf['port'].'/'. $this->conf['dbname']));
            } else {
                $this->conn = (new \MongoDB\Client('mongodb://'.$this->conf['username'].':'.$this->conf['password'].'@'.$this->conf['host'].':'.$this->conf['port'].'/'.$this->conf['dbname']));
            }

            if (!$this->conn->getServers()) {
                $this->outputs['status'] = 'ERROR';
                $this->outputs['remark'] = 'Can\'t connect to database';
            }

        }catch (\Exception $e) {
            $this->outputs['status'] = 'ERROR';
            $this->outputs['remark'] = 'Can\'t connect to database : ' . $e->getMessage();
        }

        return $this;

    }
    public function query($filter = [])
    {
        $this->outputs['service'] = 'Check Query Datas';
        // Query
        try {
            if (!$this->conn->getServers()) {
                $this->outputs['status'] = 'ERROR';
                $this->outputs['remark'] = 'Can\'t connect to database';

                return $this;
            }

            $query = new \MongoDB\Driver\Query($filter);

            $rows = $this->conn->executeQuery($this->conf['dbname'].".".$this->conf['collection'], $query);

            if (!$rows) {
                $this->outputs['status'] = 'ERROR';
                $this->outputs['remark'] = 'Can\'t query datas';
            }
        } catch (\Exception $e) {
            $this->outputs['status'] = 'ERROR';
            $this->outputs['remark'] = 'Can\'t query datas : ' . $e->getMessage();
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
