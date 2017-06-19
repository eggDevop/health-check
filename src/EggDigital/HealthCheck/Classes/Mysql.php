<?php
namespace EggDigital\HealthCheck\Classes;

use PDO;

class Mysql extends Base
{
    private $conn;

    public function __construct()
    {
        parent::__construct();
        
        $this->outputs['module'] = 'Mysql';
        $this->conf = ['host', 'username', 'password', 'dbname'];
    }

    public function connect($conf)
    {
        $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Require parameter (' . implode(',', $this->conf) . ')';

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            // Connect to mysql
            $this->conn = new PDO("mysql:host={$conf['host']};dbname={$conf['dbname']};charset=utf8", $conf['username'], $conf['password']);
            
            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!$this->conn) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t connect to database';
            }
        } catch (PDOException $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t connect to database : ' . $e->getMessage();
        }

        return $this;
    }

    public function query($sql)
    {
        $this->outputs['service'] = 'Check Query Datas';

        if (!$this->conn) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t connect to database';

            return $this;
        }

        // Query
        try {
            $this->conn->query($sql);
        } catch (PDOException  $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t query datas : ' . $e->getMessage();
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
