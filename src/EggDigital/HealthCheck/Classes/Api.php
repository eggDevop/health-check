<?php
namespace EggDigital\HealthCheck\Classes;

class Api extends Base
{
    public function __construct()
    {
        parent::__construct();
        
        $this->outputs['module'] = 'Api';
    }

    public function curlGet($url)
    {
        $this->outputs['service'] = 'Check Api';
        $this->outputs['url']     = $url;

        try {
            $ch = curl_init();  
        
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            $output = curl_exec($ch);
        
            curl_close($ch);

            if (!$output) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t get url';
            }

            return $this;
        } catch(Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t get url : ' . $e->getMessage();
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}