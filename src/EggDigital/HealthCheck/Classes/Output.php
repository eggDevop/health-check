<?php
namespace EggDigital\HealthCheck\Classes;

class Output
{
    public function html($datas, $title)
    {
        return $this->getTable($datas, $title);
    }

    private function getTable($datas, $title)
    {
        $html =
            $this->getTitle($title) .
            "<table id=\"responsive-example-table\" class=\"large-only\" cellspacing=\"0\" style=\"margin-bottom:50px;\">
                <tbody>
                    <tr align=\"left\">
                        <th width=\"12%\"></th>
                        <th width=\"30%\">Service</th>
                        <th width=\"30%\">Url</th>
                        <th width=\"12%\">Time(s)</th>
                        <th width=\"12%\">Status</th>
                        <th width=\"12%\">Remark</th>
                    </tr>\n"
                    . $this->getRows($datas) .
                "</tbody>
            </table>";

        return $html;
    }

    private function getTitle($title)
    {
        return (!empty($title)) ? "<h2>{$title}</h2>" : '';
    }

    private function getRows($datas)
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
