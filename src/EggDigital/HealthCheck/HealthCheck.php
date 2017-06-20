<?php
namespace EggDigital\HealthCheck;

class HealthCheck
{
    public static function check($service, $module_name = null)
    {
        $classes = ['cassandra', 'file', 'gearman', 'mongo', 'mysql', 'oracle', 'redis', 'socket', 'curl'];

        if (in_array(strtolower($service), $classes)) {
            return Self::$service($module_name);
        }

        echo 'Class Name Does Not Exists';
    }

    public static function output($datas)
    {
        $output = new \EggDigital\HealthCheck\Classes\Output;
        return $output->html($datas);
    }

    private static function cassandra($module_name)
    {
        $cassandra = new \EggDigital\HealthCheck\Classes\Cassandra;
        return $cassandra($module_name);
    }

    private static function file($module_name)
    {
        $file = new \EggDigital\HealthCheck\Classes\File;
        return $file($module_name);
    }

    private static function gearman($module_name)
    {
        $gearman = new \EggDigital\HealthCheck\Classes\Gearman;
        return $gearman($module_name);
    }

    private static function mongo($module_name)
    {
        $mongo = new \EggDigital\HealthCheck\Classes\Mongo;
        return $mongo($module_name);
    }

    private static function mysql($module_name)
    {
        $mysql = new \EggDigital\HealthCheck\Classes\Mysql;
        return $mysql($module_name);
    }

    private static function oracle($module_name)
    {
        $oracle = new \EggDigital\HealthCheck\Classes\Oracle;
        return $oracle($module_name);
    }

    private static function redis($module_name)
    {
        $redis = new \EggDigital\HealthCheck\Classes\Redis;
        return $redis($module_name);
    }

    private static function socket($module_name)
    {
        $socket = new \EggDigital\HealthCheck\Classes\Socket;
        return $socket($module_name);
    }

    private static function curl($module_name)
    {
        $curl = new \EggDigital\HealthCheck\Classes\Api;
        return $curl($module_name);
    }
}
