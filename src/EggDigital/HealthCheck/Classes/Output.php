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
                width: 30px;
                height: 30px;
                background: red;
                -moz-border-radius: 50px;
                -webkit-border-radius: 50px;
                border-radius: 50px;
           }
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
            <div class="container-fluid">'
            . $this->getTitle($title)
            . $this->getTable($datas)
            . $this->getSummary($datas) .
            '</div>';

        return $body;
    }

    private function getFooter()
    {
        $footer = '
            <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
            <form>&nbsp;<input type=button value="Refresh" onClick="javascript:location.reload();"></form>
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

                if ($data['status'] !== 'OK') {
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
                        <th></th>
                        <th></th>
                        <th>Service</th>
                        <th>Url</th>
                        <th>Time(s)</th>
                        <th>Status</th>
                        <th>Remark</th>
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

    private function getTableRows($datas)
    {
        $html = '';
        foreach ($datas as $value) {
            $html .= '<tr>
                        <td>';

            $html .=  ($value['status'] === 'OK' ) 
                ? '<center><div class="circle" style="background-color: green"></div></center>'
                : '<center><div class="circle blink" ></div></center>';

            $html .= '</td>';
            $html .= '<td>'.$value['module'].'</td>';
            $html .= '<td>'.$value['service'].'</td>';
            $html .= '<td>'.$value['url'].'</td>';
            $html .= '<td>'.$value['response'].'</td>';
            $html .= '<td>'.$value['status'].'</td>';
            $html .= '<td>'.$value['remark'].'</td>';
            $html .= '</tr>';
        }

        return $html;
    }
}
