<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Mongo extends Base
{
    private $conn;
    private $conf;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Mongo';
        $this->require_config = ['host', 'port', 'dbname'];
    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';

        $this->conf = $conf;
        // Validate parameter
        if (false === $this->validParams($this->conf)) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Require parameter (' . implode(',', $this->require_config) . ')',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Set url
        $this->outputs['url'] = "{$this->conf['host']}:{$this->conf['port']}";

        try {
            $this->conn = (empty($this->conf['username']) && empty($this->conf['password']))
                ? new \MongoDB\Driver\Manager("mongodb://{$this->conf['host']}:{$this->conf['port']}/{$this->conf['dbname']}")
                : new \MongoDB\Client("mongodb://{$this->conf['username']}:{$this->conf['password']}@{$this->conf['host']}:{$this->conf['port']}/{$this->conf['dbname']}");

            // $mongodb = (new \MongoDB\Client('mongodb://'.$this->conf['host'].':'.$this->conf['port'].'/'. $this->conf['dbname']));

            if (!$this->conn->getServers()) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t connect to database',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (\Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t connect to database : ' . $e->getMessage(),
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
    public function query($filter = [])
    {
        $this->outputs['service'] = 'Check Query Datas';

        // Query
        try {
            if (!$this->conn->getServers()) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t connect to database',
                    'response' => $this->start_time
                ]);

                return $this;
            }

            $query = new \MongoDB\Driver\Query($filter);

            $rows = $this->conn->executeQuery("{$this->conf['dbname']}.{$this->conf['collection']}", $query);

            if (!$rows) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t query datas',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (\Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t query datas : ' . $e->getMessage(),
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
