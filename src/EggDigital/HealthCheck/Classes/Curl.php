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

    public function curlGet($urls)
    {
        $this->outputs['service'] = 'Check Curl Get';

        foreach ($urls AS $url) {
            $this->outputs['url'] .= $url . '<br>';

            try {
                $ch = curl_init();
            
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
                $output    = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
                curl_close($ch);

                if ($http_code !== 200) {
                    $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                    $this->outputs['remark'] .= '<br><span class="error">Can\'t get url</span>';
                } else {
                    // Success
                    $this->outputs['status'] .= '<br>OK';
                    $this->outputs['remark'] .= '<br>';
                }
            } catch (Exception $e) {
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= '<br><span class="error">Can\'t get url : ' . $e->getMessage() . '</span>';
            }
        }
        
        $this->outputs['response'] += (microtime(true) - $this->start_time);
        
        return $this;
    }
}
