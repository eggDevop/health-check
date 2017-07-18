<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class File extends Base
{
    private $handle;
    private $file;
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
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= "<br><span class=\"error\">Directory {$path} Does Not Exists!</span>";
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        $file_name = 'TEST_' . date('Y-m-d') . '.txt';
        $file = "{$path}/{$file_name}";
        $handle = $this->openFile($file, 'ab');
        
        try {
            $written = fwrite($handle, "TEST WRITE FILE");

            fclose($handle);
        } catch (Exception $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Write File : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        if (!$written) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Write File</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

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
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= "<br><span class=\"error\">Directory {$path} Does Not Exists!</span>";
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }

            // Check File exists
            if (!$this->fileExists($file)) {
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= '<br><span class="error">File Not Found!</span>';
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }

            $handle = $this->openFile($file, 'rb');

            $contents = fread($handle, filesize($file));

            fclose($handle);

            if (!$contents) {
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= '<br><span class="error">Can\'t Read File</span>';
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Read File : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

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
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= "<br><span class=\"error\">Directory {$path} Does Not Exists!</span>";
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }
        }

        // Set file
        $file1 = "{$path1}/{$file_name1}";
        $file2 = "{$path2}/{$file_name2}";

        // Check file exists
        foreach ([$file1, $file2] as $file) {
            if (!$this->fileExists($file1)) {
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= "<br><span class=\"error\">File {$file} Not Found!</span>";
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }
        }

        try {
            // Compare file size
            if (filesize($file1) !== filesize($file2)) {
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= '<br><span class="error">Size of File is Not Equal!</span>';
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }

            // Compare md5
            if (md5_file($file1) !== md5_file($file2)) {
                $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
                $this->outputs['remark']   .= '<br><span class="error">Content of File is Not Equal!</span>';
                $this->outputs['response'] += (microtime(true) - $this->start_time);

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Can\'t Compare File : ' . $e->getMessage() . '</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

        return $this;
    }

    // Method for get file extension
    public function extensionFile($path, $file_name, $ext)
    {
        $file = "{$path}/{$file_name}";

        $this->outputs['service'] = 'Check Extension File';
        $this->outputs['url']     = $file;

        if ($ext !== $this->getExtension($file)) {
            $this->outputs['status']   .= '<br><span class="error">ERROR</span>';
            $this->outputs['remark']   .= '<br><span class="error">Extension File Not Match</span>';
            $this->outputs['response'] += (microtime(true) - $this->start_time);

            return $this;
        }

        // Success
        $this->outputs['status']   .= '<br>OK';
        $this->outputs['remark']   .= '<br>';
        $this->outputs['response'] += (microtime(true) - $this->start_time);

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
            $this->outputs['url'] .= "{$path}<br>";

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

            if ($total_file === 0) {
                $this->outputs['status'] .= '<br>OK';
                $this->outputs['remark'] .= '<br>';
                continue;
            }

            $now = date('Y-m-d H:i:s');

            // Check file remain
            foreach ($files as $file) {
                $modify     = date("Y-m-d H:i:s", filemtime("{$path}/{$file}"));
                $diff_min   = $this->dateDifference($now, $modify);

                // Check different min
                if ($diff_min > $min) {
                    $this->outputs['status'] .= '<br><span class="error">ERROR</span>';
                    $this->outputs['remark'] .= "<br><span class=\"error\">File {$file} is remain!</span>";
                } else {
                    $this->outputs['status'] .= '<br>OK';
                    $this->outputs['remark'] .= '<br>';
                }
            }
        }

        $this->outputs['response'] += (microtime(true) - $this->start_time);
        
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
}
