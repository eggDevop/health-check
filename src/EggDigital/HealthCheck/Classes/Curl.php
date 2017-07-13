<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Curl extends Base
{
    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'Curl';
    }

    public function curlGet($url)
    {
        $this->outputs['service'] = 'Check Curl Get';
        $this->outputs['url']     = $url;

        try {
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            $output = curl_exec($ch);
        
            curl_close($ch);

            if (!$output) {
                $this->outputs['status']  = '<span class="error">ERROR</span>';
                $this->outputs['remark']  = '<span class="status-error">Can\'t get url</span>';
            }
        } catch (Exception $e) {
            $this->outputs['status']  = '<span class="error">ERROR</span>';
            $this->outputs['remark']  = '<span class="status-error">Can\'t get url : ' . $e->getMessage() . '</span>';
        }

        return $this;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
