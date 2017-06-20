<?php
namespace EggDigital\HealthCheck\Classes;

class Output
{
    public function html($datas, $title)
    {
        $html = '
            <!DOCTYPE html>
            <html lang="en">
                <head>'
                . $this->getHeader() .
                '</head>
                <body>'
                . $this->getTitle($title)
                . $this->getBody($datas)
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
        ';

        return $header;
    }

    private function getTitle($title)
    {
        return (!empty($title)) ? "<h1>{$title}</h1>" : '';
    }

    private function getBody($datas)
    {
        $body = '
            <div id="wrapper">'
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
        ';

        return $footer;
    }

    private function getSummary($datas)
    {
        $status = true;

        foreach ($datas as $data) {
            if (!isset($data['status'])) {
                continue;
            }

            if ($data['status'] !== 'OK') {
                $status = false;
                break;
            }
        }

        return ($status) ? '<br><br>THIS_PAGE_IS_COMPLETELY_LOADED' : '';
    }

    private function getTable($datas)
    {
        $table = '';
        foreach ($datas as $title => $data) {
            $table .=
            $this->getTableTitle($title) .
            '<table class="table table-striped table-hover table-responsive">
                <thead class="thead-inverse">
                    <tr>
                        <th width="12%"></th>
                        <th width="30%">Service</th>
                        <th width="30%">Url</th>
                        <th width="12%">Time(s)</th>
                        <th width="12%">Status</th>
                        <th width="12%">Remark</th>
                    </tr>
                </thead>
                <tbody>'
                    . $this->getTableRows($data) .
                '</tbody>
            </table><br>';
        }

        return $table;
    }

    private function getTableTitle($title)
    {
        return (!empty($title)) ? "<h2>{$title}</h2>" : '';
    }

    private function getTableRows($datas)
    {
        $html = '';
        foreach ($datas as $value) {
            $html .=
                "<tr>
                    <td>{$value['module']}</td>
                    <td>{$value['service']}</td>
                    <td>{$value['url']}</td>
                    <td>{$value['response']}</td>
                    <td>{$value['status']}</td>
                    <td>{$value['remark']}</td>
                </tr>\n";
        }

        return $html;
    }
}
