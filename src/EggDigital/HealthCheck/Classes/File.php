<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class File extends Base
{
    private $handle;
    private $start_time;

    public function __construct($module_name = null)
    {
        $this->start_time = microtime(true);

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'File';
    }

    // Method for write file
    public function writeFile($path)
    {
        $this->outputs['service'] = 'Check Write File';
        $this->outputs['url']     = $path;

        // Check directory exists
        if (!$this->pathFileExists($path)) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => "Directory {$path} Does Not Exists!",
                'response' => $this->start_time
            ]);

            return $this;
        }

        $file_name = 'TEST_' . date('Y-m-d') . '.txt';
        $file = "{$path}/{$file_name}";
        $handle = $this->openFile($file, 'ab');
        
        try {
            $written = fwrite($handle, "TEST WRITE FILE");

            fclose($handle);
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Write File : ' . $e->getMessage(),
                'response' => $this->start_time
            ]);

            return $this;
        }

        if (!$written) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Write File',
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

    // Method for read file
    public function readFile($path, $file_name)
    {
        $this->outputs['service'] = 'Check Read File';
        $this->outputs['url']     = $path;

        $file = "{$path}/{$file_name}";

        try {
            // Check directory exists
            if (!$this->pathFileExists($path)) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => "Directory {$path} Does Not Exists!",
                    'response' => $this->start_time
                ]);

                return $this;
            }

            // Check File exists
            if (!$this->fileExists($file)) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'File Not Found!',
                    'response' => $this->start_time
                ]);

                return $this;
            }

            $handle = $this->openFile($file, 'rb');

            $contents = fread($handle, filesize($file));

            fclose($handle);

            if (!$contents) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Can\'t Read File',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Read File : ' . $e->getMessage(),
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

    public function deleteFile()
    {
    }

    // Method for compare file
    public function compareFiles($path1, $path2, $file_name1, $file_name2)
    {
        $this->outputs['service'] = 'Check Compare File';
        $this->outputs['url']     = "File (1) : \"{$path1}/{file_name1}\"<br>File (2) : \"{$path2}/{file_name2}\"";

        // Check directory exists
        foreach ([$path1, $path2] as $path) {
            if (!$this->pathFileExists($path)) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => "Directory {$path} Does Not Exists!",
                    'response' => $this->start_time
                ]);

                return $this;
            }
        }

        // Set file
        $file1 = "{$path1}/{$file_name1}";
        $file2 = "{$path2}/{$file_name2}";

        // Check file exists
        foreach ([$file1, $file2] as $file) {
            if (!$this->fileExists($file1)) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => "File {$file} Not Found!",
                    'response' => $this->start_time
                ]);

                return $this;
            }
        }

        try {
            // Compare file size
            if (filesize($file1) !== filesize($file2)) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Size of File is Not Equal!',
                    'response' => $this->start_time
                ]);

                return $this;
            }

            // Compare md5
            if (md5_file($file1) !== md5_file($file2)) {
                $this->setOutputs([
                    'status'   => 'ERROR',
                    'remark'   => 'Content of File is Not Equal!',
                    'response' => $this->start_time
                ]);

                return $this;
            }
        } catch (Exception $e) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Can\'t Compare File : ' . $e->getMessage(),
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

    // Method for get file extension
    public function extensionFile($path, $file_name, $ext)
    {
        $file = "{$path}/{$file_name}";

        $this->outputs['service'] = 'Check Extension File';
        $this->outputs['url']     = $file;

        if ($ext !== $this->getExtension($file)) {
            $this->setOutputs([
                'status'   => 'ERROR',
                'remark'   => 'Extension File Not Match',
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

    // Method for check remain file
    public function remainFile($paths, $min)
    {
        $this->outputs['service'] = 'Check Remain File';

        // Set path to array
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        
        foreach ($paths as $path) {

            // Check directory exists
            if (!$this->pathFileExists($path)) {
                $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark'] .= "<br><span class=\"error\">Directory {$path} Does Not Exists!</span>";
                continue;
            }

            // Scan file in path
            $files      = scandir($path, 1);
            $files      = array_diff($files, ['.', '..']);
            $total_file = count($files);

            $this->outputs['url'] .= "{$path}  (Total File: {$total_file})<br>"; 

            if ($total_file === 0) {
                $this->setOutputs([
                    'status'   => 'OK',
                    'remark'   => '',
                    'response' => $this->start_time
                ]);
                continue;
            }

            $now = date('Y-m-d H:i:s');

            // Check file remain
            foreach ($files as $file) {
                $modify     = date("Y-m-d H:i:s", filemtime("{$path}/{$file}"));
                $diff_min   = $this->dateDifference($now, $modify);

                // Check different min
                if ($diff_min > $min) {
                    $this->setOutputs([
                        'status'   => 'ERROR',
                        'remark'   => "File {$file} is remain!",
                        'response' => $this->start_time
                    ]);
                    continue;
                }

                $this->setOutputs([
                    'status'   => 'OK',
                    'remark'   => '',
                    'response' => $this->start_time
                ]);
            }
        }
        
        return $this;
    }

    //========== Start : Support Method ==========/

    // Method for diff date
    private function dateDifference($date_1, $date_2, $differenceFormat = '%i')
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        
        $interval = date_diff($datetime1, $datetime2);
        
        return (int) $interval->format($differenceFormat);
    }

    // Method for check path file
    private function pathFileExists($path)
    {
        return (is_dir($path)) ? true : false;
    }

    private function fileExists($file)
    {
        return (is_file($file)) ? true : false;
    }

    // Method for open file
    private function openFile($file, $mode)
    {
        return fopen($file, $mode);
    }

    public function deleteData()
    {
    }

    // Method for count line
    public function countLines()
    {
        $count = 0;
        while (!$this->handle->eof()) {
            $line = $this->handle->fgets();
            if (empty($line)) {
                continue;
            }
            ++$count;
        }

        return $count;
    }

    // Method for get file extension
    private function getExtension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    // Method for get last line
    private function getLastLine($file)
    {
        $line   = '';
        $handle = $this->openFile($file, 'rb');
        $cursor = -1;

        fseek($handle, $cursor, SEEK_END);
        $char = fgetc($handle);

        // Trim trailing newline chars of the file
        while ($char === "\n" || $char === "\r") {
            fseek($handle, --$cursor, SEEK_END);
            $char = fgetc($handle);
        }

        // Read until the start of file or first newline char
        while ($char !== false && $char !== "\n" && $char !== "\r") {
            // Prepend the new char
            $line = "{$char}{$line}";
            fseek($handle, --$cursor, SEEK_END);
            $char = fgetc($handle);
        }

        fclose($handle);

        return $line;
    }

    /*
     * Method for get files size in folder
     * return $output = [
     *   'file_name1' => 'file_size1',
     *   'file_name2' => 'file_size2',
     *   'file_name3' => 'file_size3' 
     *   ]
     */
    private function getFilesAndSizeInFolder($folder)
    {
        $outputs = [];

        $files = scandir($folder);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            if (!isset($outputs[$file])) {
                $outputs[$file] = 0;
            }

            $outputs[$file] = filesize("{$folder}/{$file}");
        }

        return $outputs;
    }

    //========== Start : Support Method ==========/

    public function sftpconnect($conf)
    {
        $this->outputs['service'] = 'Check Connection';
        foreach ($conf['pathfileshealthcheck'] as $key => $value) {
            $jsondata = json_decode(file_get_contents($value));
            $currentDate = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")));
            $periodcurrentDate = date("Y-m-d H:i:s", strtotime('-3 minutes'));
            $flagDate = date("Y-m-d H:i:s", strtotime($jsondata->status->datetime));
 
            if (isset($jsondata)) {
                if (($flagDate >= $periodcurrentDate) && ($flagDate <= $currentDate)){
                    foreach ($jsondata->status->data as $k => $value) {
                        $service .= $key . ' ➡ SFTP Path' . '<br>';
                        if ($value == "ok") { 
                            $url .= $k . '<br>';
                            $status .= '<br>'. 'OK';
                            $remark .= '<br>';
                        }else{
                            $url .= $k . '<br>';
                            $status .= '<br><span class="error">ERROR</span>';
                            $remark .= '<br><span class="error">'. $key . ' ➡ SFTP path can not connect.</span>';
                        }
                    }
                }else{
                    $service .= $key . ' ➡ SFTP Path' . '<br>';
                    $url .= $k . '<br>';
                    $status .= '<br><span class="error">ERROR</span>';
                    $remark .= '<br><span class="error">Health check not update.</span>';   
                }
            }else{
                $service .= $key . ' ➡ SFTP Path' . '<br>';
                $url .= $k . '<br>';
                $status .= '<br><span class="error">ERROR</span>';
                $remark .= '<br><span class="error">Health check files status not found.</span>';
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
