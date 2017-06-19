<?php
namespace EggDigital\HealthCheck;

class HealthCheck
{
    public static function check($service)
    {
        $classes = ['cassandra', 'file', 'gearman', 'mongo', 'mysql', 'oracle', 'redis', 'socket'];

        if (in_array(strtolower($service), $classes)) {
            return Self::$service();
        }

        echo 'Class name does not exists';
    }

    public static function output($datas, $title = null)
    {
        $output = new \EggDigital\HealthCheck\Classes\Output;
        return $output->html($datas, $title);
    }

    private static function cassandra()
    {
        return new \EggDigital\HealthCheck\Classes\Cassandra;
    }

    private static function file()
    {
        return new \EggDigital\HealthCheck\Classes\File;
    }

    private static function gearman()
    {
        return new \EggDigital\HealthCheck\Classes\Gearman;
    }

    private static function mongo()
    {
        return new \EggDigital\HealthCheck\Classes\Mongo;
    }

    private static function mysql()
    {
        return new \EggDigital\HealthCheck\Classes\Mysql;
    }

    private static function oracle()
    {
        return new \EggDigital\HealthCheck\Classes\Oracle;
    }

    private static function redis()
    {
        return new \EggDigital\HealthCheck\Classes\Redis;
    }

    private static function socket()
    {
        return new \EggDigital\HealthCheck\Classes\Socket;
    }
}
