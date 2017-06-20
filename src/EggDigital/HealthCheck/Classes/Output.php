<?php
namespace EggDigital\HealthCheck\Classes;

class Output
{
    private $theme = 'stacktable';

    public function html($datas)
    {
        $html = '
            <!DOCTYPE html>
            <html lang="en">
                <head>'
                . $this->getHader() .
                '</head>
                <body>'
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
            <link href="http://fonts.googleapis.com/css?family=Courgette">
            <link href="' . dirname(__FILE__) . "/../themes/{$theme}/{$theme}.css" . 'rel="stylesheet">';

        return $header;
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
            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
            <!-- <script>window.jQuery || document.write(\'<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"><\/script>\')</script> </script> -->
            <script src="' . dirname(__FILE__) . "/../themes/{$theme}/{$theme}.js" . '"></script>

            <script>
            $("#responsive-example-table").stacktable({myClass:"your-custom-class"});
            </script>
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
            '<table id="responsive-example-table" class="large-only" cellspacing="0" style="margin-bottom:50px;">
                <tbody>
                    <tr align="left">
                        <th width="12%"></th>
                        <th width="30%">Service</th>
                        <th width="30%">Url</th>
                        <th width="12%">Time(s)</th>
                        <th width="12%">Status</th>
                        <th width="12%">Remark</th>
                    </tr>'
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
                "<tr align=\"left\">
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
