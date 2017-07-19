<?php
namespace EggDigital\HealthCheck\Classes;

class Output
{
    public function html($datas, $title = null)
    {
        $html = '
            <!DOCTYPE html>
            <html lang="en">
                <head>'
                . $this->getHeader() .
                '</head>
                <body>'
                . $this->getBody($datas, $title)
                . $this->getFooter() .
                '</body>
            </html>';

            return $html;
    }

    private function getHeader()
    {
        $header = '
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>Health Check</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
            <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
            <title>Document</title>
            <style>
            .blink {
                animation: blinker 1s linear infinite;
            }
            @keyframes blinker {  
                50% { opacity: 0; }
            }

            .circle {
                width: 20px;
                height: 20px;
                -moz-border-radius: 50px;
                -webkit-border-radius: 50px;
                border-radius: 50px;
            }
            .circle-success { background: green; }
            .circle-error { background: red; }

            .error { color: red; }

            td { white-space: nowrap; }
            
            </style>
        ';

        return $header;
    }

    private function getTitle($title)
    {
        return (!empty($title)) ? "<h3 class=\"text-center\">{$title}</h3>" : '';
    }

    private function getBody($datas, $title)
    {
        $body = '
            <div class="container-fluid" style="padding-top:2em;">'
            . $this->getTitle($title)
            . $this->getTable($datas)
            . $this->getSummary($datas) .
                '<br><br><br>' .
            '</div>';

        return $body;
    }

    private function getFooter()
    {
        $footer = '
            <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
        ';

        return $footer;
    }

    private function getSummary($datas)
    {
        $status = true;

        foreach ($datas as $modules) {
            foreach ($modules as $data) {
                if (!isset($data['status'])) {
                    continue;
                }

                if (strpos($data['status'], 'ERROR')) {
                    $status = false;
                    break;
                }
            }
        }

        return ($status) ? 'THIS_PAGE_IS_COMPLETELY_LOADED' : '';
    }

    private function getTable($datas)
    {
        $table = '';
        foreach ($datas as $title => $data) {
            $table .=
            $this->getTableTitle($title) .
            '<table class="table table-sm table-striped table-hover table-responsive table-sm">
                <thead class="thead-inverse">
                    <tr>
                        <th colspan="2" width="125px"></th>
                        <th width="95400pxpx" class="text-center">Service</th>
                        <th width="570px" class="text-center">Url</th>
                        <th width="80px" class="text-center">Time(s)</th>
                        <th width="100px" class="text-center">Status</th>
                        <th width="" class="text-center">Remark</th>
                    </tr>
                </thead>
                <tbody>'
                    . $this->getTableRows($data) .
                '</tbody>
            </table><br>';
        }

        return $table;
    }

    private function getTableTitle($title = null)
    {
        return (!empty($title)) ? "<h3>{$title}</h3>" : '';
    }

    private function removeFristBr($val)
    {
        $len = strlen($val) - 1;
        $br  = substr($val, 0, 4);

        if ($br === '<br>') {
            return substr($val, 4, $len);
        }
        
        return null;
    }

    private function getTableRows($datas)
    {
        $html = '';
        foreach ($datas as $value) {
            $html .= '<tr>
                        <td width="30px">';

            $html .= (strpos($value['status'], 'ERROR') === false )
                ? '<center><div class="circle circle-success"></div></center>'
                : '<center><div class="circle circle-error blink"></div></center>';

            // Remove tag <br>, if it frist
            $value['status']   = $this->removeFristBr($value['status']);
            $value['remark']   = $this->removeFristBr($value['remark']);
            $value['response'] = number_format($value['response'], 4, '.', ',');

            $html .= "</td>
                <td width=\"95px\">{$value['module']}</td>
                <td width=\"400px\">{$value['service']}</td>
                <td width=\"570px\">{$value['url']}</td>
                <td width=\"80px\" class=\"text-center\">{$value['response']}</td>
                <td width=\"100px\" class=\"text-center\">{$value['status']}</td>
                <td width=\"\">{$value['remark']}</td>
            </tr>";
        }

        return $html;
    }
}
