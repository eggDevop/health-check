<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class Curl extends Base
{
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

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
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="error">Can\'t get url</span>';

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark'] .= '<br><span class="error">Can\'t get url : ' . $e->getMessage() . '</span>';

            return $this;
        }

        // Success
        $this->outputs['status'] .= '<br>OK';
        $this->outputs['remark'] .= '<br>';
        
        return $this;
    }

    public function __destruct()
    {
        $this->outputs['response'] += (microtime(true) - $this->start_time);
    }
}
