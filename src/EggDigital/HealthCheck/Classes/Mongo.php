<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Mongo extends Base
{
    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Mongo';
        $this->require_config = ['host', 'port', 'dbname'];
    }

    public function connect($hostname, $port, $database, $username = null, $password = null)
    {
        $this->setUrl($hostname.':'.$port);

        if (empty($username) && empty($password)) {
            $mongo = new Mongo('mongodb://'.$hostname.':'.$port.'/'. $database);
        } else {
            $mongo = new Mongo('mongodb://'.$username.':'.$password.'@'.$hostname.':'.$port.'/'.$database);
        }

        return $mongo;
    }

    public function getData($mongo, $database, $collection)
    {
        $db         = $mongo->selectDB($database);
        $collection = new MongoCollection($db, $collection);
        $cursor     = $collection->findOne();

        return $cursor;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
