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
        $this->outputs['url']     = "{$url}";

        try {
            $ch = curl_init();

            if (is_resource($ch) === false) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t post url : is resource fail',
                    'response' => $this->start_time
                ]);
                
                return $this;
            }

            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_NOBODY, false);
        
            $result    = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
            curl_close($ch);
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t get url : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);
            
            return $this;
        }

        if ($http_code !== 200) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t get url http code not 200',
                'response' => $this->start_time
            ]);
            
            return $this;
        }

        if (empty($result) || !is_object($result)) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t get url',
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

    public function curlPost($url, $params = [])
    {
        $this->outputs['service'] = 'Check Curl Post';
        $this->outputs['url']     = "{$url}";

        $result = '';

        try {
            $ch = curl_init($url);

            if (is_resource($ch) === false) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t post url : is resource fail',
                    'response' => $this->start_time
                ]);
                
                return $this;
            }

            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t post url : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);
            
            return $this;
        }

        if ($http_code !== 200) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t post url http code not 200',
                'response' => $this->start_time
            ]);

            return $this;
        }

        if (empty($result) || !is_object($result)) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t post url',
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
