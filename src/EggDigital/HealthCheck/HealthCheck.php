<?php
namespace EggDigital\HealthCheck;

class HealthCheck
{
    public static function check($service, $module_name = null)
    {
        $classes = ['cassandra', 'file', 'gearman', 'mongo', 'mysql', 'oracle', 'redis', 'socket', 'curl', 'rabbitmq'];

        if (in_array(strtolower($service), $classes)) {
            return Self::$service($module_name);
        }

        echo 'Class Name Does Not Exists';
    }

    public static function output($datas, $title = null)
    {
        $output = new \EggDigital\HealthCheck\Classes\Output;
        return $output->html($datas, $title);
    }

    private static function cassandra($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Cassandra($module_name);
    }

    private static function file($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\File($module_name);
    }

    private static function gearman($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Gearman($module_name);
    }

    private static function mongo($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Mongo($module_name);
    }

    private static function mysql($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Mysql($module_name);
    }

    private static function oracle($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Oracle($module_name);
    }

    private static function redis($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Redis($module_name);
    }

    private static function socket($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Socket($module_name);
    }

    private static function curl($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\Curl($module_name);
    }

    private static function rabbitmq($module_name)
    {
        return new \EggDigital\HealthCheck\Classes\RabbitMQ($module_name);
    }
}
