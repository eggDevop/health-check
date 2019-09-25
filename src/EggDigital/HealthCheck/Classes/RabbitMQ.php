<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

class RabbitMQ extends Base
{
    private $connection;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'RabbitMQ';
        $this->require_config = ['host', 'port', 'username', 'password'];
    }

    // Method for check connect to rabbitmq
    public function connect($conf)
    {
         $this->outputs['service'] = 'Check Connection';

        // Validate parameter
        if (false === $this->validParams($conf)) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Require parameter (' . implode(',', $this->require_config) . ')',
                'response' => $this->start_time
            ]);

            return $this;
        }

        // Set url
        $this->outputs['url'] = $conf['host'];

        try {
            $this->connection = new AMQPStreamConnection($conf['host'], $conf['port'], $conf['username'], $conf['password']);
            
            // Check status rabbitmq
            if (!$this->connection->isConnected()) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Connect to RabbitMQ',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to RabbitMQ : ' . $e->getMessage(),
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

    // Method for get total queue in rabbitmq
    public function queue($queue_name, $max_job = null, $check_worker = false)
    {
        if (!$this->connection) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to RabbitMQ',
                'response' => $this->start_time
            ]);

            return $this;
        }

        try {
            $this->channel = $this->connection->channel();

            list(,$msg_count, $consumer_count) = $this->channel->queue_declare($queue_name, true, false, false, false);

        } catch (AMQPProtocolChannelException $e) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b>",
                'url'      => '',
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Get Queue Name : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);

            // Re connect channel
            $this->channel = $this->connection->channel();

            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Check Max Queue
        if (isset($max_job) && $msg_count > $max_job) {
            $this->setOutputs([
                'service'  => "Number of Queue <b>{$queue_name}</b> : {$msg_count}</b>",
                'url'      => "Number of Worker (Total: {$consumer_count})",
                'status'   => 'ERROR',
                'remark'   => "Queues > {$max_job}",
                'response' => $this->start_time
            ]);
            
            return $this;
        }

        // Success
        $this->setOutputs([
            'service'  => "Number of Queue <b>{$queue_name}</b> : {$msg_count}",
            'url'      => "Number of Worker (Total: {$consumer_count})",
            'status'   => 'OK',
            'remark'   => !empty($max_job) ? "Queues > {$max_job} alert" : '',
            'response' => $this->start_time
        ]);

        return $this;
    }

    public function apiconnect($conf)
    {
        $this->outputs['service'] = 'Check Connection';
        $checkRabbbitConnect = $this->curlAPI($conf,$conf['apihealthcheck']['apichkconnect']);

        if (!$checkRabbbitConnect['response']) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Connect to RabbitMQ',
                'response' => $this->start_time
            ]);

            return $this;
        }

        $checkRabbbitConnects = json_decode($checkRabbbitConnect['response']);

        if ($checkRabbbitConnects->error) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Not authorised',
                'response' => $this->start_time
            ]);

            return $this;
        }

        foreach ($checkRabbbitConnects as $key => $value) {
            $this->setOutputs([
                'RabbitMQ Server' => [
                        'service' =>'RabbitMQ Server Status',
                        'url' => $conf['host']." (".$value->name.")"
                    ]
            ]);
            if ($value->running == true) { 
                $status .= '<br>'. 'OK';
                $remark .= '<br>';
            }else{
                $status .= '<br><span class="error">ERROR</span>';
                $remark .= '<br><span class="error">'. $value->name .' not working.</span>';
            }
        }

        $this->setOutputs([
            'RabbitMQ Server' => [
                'status' => $status,
                'remark' => $remark,
                'response' => $checkRabbbitConnect['info']['total_time']
            ]
        ]);

        return $this;
    }

    public function apiqueue($queue_name, $max_job = null, $conf)
    {
        $checkRabbbitQueue = $this->curlAPI($conf,$conf['apihealthcheck']['apichkqueue']);
        $checkRabbbitQueues = json_decode($checkRabbbitQueue['response']);
        if ($checkRabbbitQueues) {
            foreach ($checkRabbbitQueues as $key => $value) {
                if ($value->name == $queue_name) {

                    if (isset($max_job) && $value->backing_queue_status->len > $max_job) {
                        $this->setOutputs([
                            'service'  => "Total queue amount of : <b>{$queue_name}</b> = {$value->backing_queue_status->len}",
                            'url'      => "Number of Worker (Total: {$value->consumers})",
                            'status'   => 'ERROR',
                            'remark'   => "Queues > {$max_job}"
                        ]);
                        
                        return $this;
                    }else{
                        $this->setOutputs([
                            'service'  => "Total queue amount of : <b>{$queue_name}</b> = {$value->backing_queue_status->len}",
                            'url'      => "Number of Worker (Total: {$value->consumers})",
                            'status'   => 'OK',
                            'remark'   => !empty($max_job) ? "Queues > {$max_job} alert" : ''
                        ]);
                    }
                }
            }
        }else{
            $this->setOutputs([
                'service'  => "Total queue amount of : <b>{$queue_name}</b> = 0",
                'url'      => "Number of Worker (Total: Not connect)",
                'status'   => 'ERROR',
                'remark'   => "Can't Connect to RabbitMQ"
            ]);
        }
        

        return $this;
    }

    public function curlAPI($data,$apitype)
    {
        $auth = base64_encode($data['username'].":".$data['password']);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_PORT => "15672",
          CURLOPT_URL => "http://".$data['host'].":".$data['port']."/".$apitype,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 10,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ".$auth,
            "Content-Type: application/x-www-form-urlencoded"
          ),
        ));

        $resp['response'] = curl_exec($curl);
        $resp['info'] = curl_getinfo($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return ($resp)?$resp:$err;
    }

    public function queueconnect($conf)
    {
        $this->outputs['service'] = 'Check Connection';
        foreach ($conf['pathfileshealthcheck'] as $key => $value) {
            $jsondata = json_decode(file_get_contents($value));
            $currentDate = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")));
            $periodcurrentDate = date("Y-m-d H:i:s", strtotime('-3 minutes'));
            $flagDate = date("Y-m-d H:i:s", strtotime($jsondata->status->datetime));

            if (($flagDate >= $periodcurrentDate) && ($flagDate <= $currentDate)){
                if (isset($jsondata)) {
                    foreach ($jsondata->status->data as $k => $value) {
                        $service .= $key . ' ➡ RebbitMQ' . '<br>';
                        if ($value == "ok") { 
                            $url .= $k . '<br>';
                            $status .= '<br>'. 'OK';
                            $remark .= '<br>';
                        }else{
                            $url .= $k . '<br>';
                            $status .= '<br><span class="error">ERROR</span>';
                            $remark .= '<br><span class="error">'. $key . ' ➡ RebbitMQ not connect.</span>';
                        }

                    }

                }else{
                    $service .= $key . ' ➡ RebbitMQ' . '<br>';
                    $url .= $k . '<br>';
                    $status .= '<br><span class="error">ERROR</span>';
                    $remark .= '<br><span class="error">Health check files status not found.</span>';
                }
            }else{
                $service .= $key . ' ➡ RebbitMQ' . '<br>';
                $url .= $k . '<br>';
                $status .= '<br><span class="error">ERROR</span>';
                $remark .= '<br><span class="error">Health check not update.</span>';
            }
        }

        $this->setOutputs([
            'RabbitMQ Server' => [
                'service' => $service,
                'url' => $url,
                'status' => $status,
                'remark' => $remark
            ]
        ]);

        return $this;
    }

}
