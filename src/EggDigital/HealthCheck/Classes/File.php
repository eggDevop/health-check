<?php
namespace EggDigital\HealthCheck\Classes;

use EggDigital\HealthCheck\Classes\Base;

class File extends Base
{
    private $handle;
    private $file;
    
    public function __construct($module_name = null)
    {
        parent::__construct();

        $this->outputs['module'] = (!empty($module_name)) ? $module_name : 'File';
    }

    // Method for write file
    public function writeFile($path)
    {
        $this->outputs['service'] = 'Check Write File';
        $this->outputs['url']     = $path;

        // Check directory exists
        if (!$this->pathFileExists($path)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Path not found!';

            return $this;
        }

        $file_name = 'TEST_' . date('Y-m-d') . '.txt';
        $file = "{$path}/{$file_name}";
        $handle = $this->openFile($file, 'ab');
        
        try {
            $written = fwrite($handle, "TEST WRITE FILE");

            fclose($handle);

            if (!$written) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t Write File';
            }
        } catch (Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Write File : ' . $e->getMessage();
        }

        return $this;
    }

    // Method for read file
    public function readFile($path, $file_name)
    {
        $this->outputs['service'] = 'Check Read File';
        $this->outputs['url']     = $path;

        try {
            $file = "{$path}/{$file_name}";

            // Check directory exists
            if (!$this->pathFileExists($path)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Directory "{$path}" Does Not Exists!';

                return $this;
            }

            // Check File exists
            if (!$this->fileExists($file)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'File Not Found!';

                return $this;
            }

            $handle = $this->openFile($file, 'rb');

            $contents = fread($handle, filesize($file));

            fclose($handle);

            if (!$contents) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t Read File';
            }
        } catch (Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Read File : ' . $e->getMessage();
        }

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
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Directory "{$path}" Does Not Exists!';

                return $this;
            }
        }

        // Set file
        $file1 = "{$path1}/{$file_name1}";
        $file2 = "{$path2}/{$file_name2}";

        // Check file exists
        foreach ([$file1, $file2] as $file) {
            if (!$this->fileExists($file1)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'File "{$file}" Not Found!';

                return $this;
            }
        }

        try {
            // Compare file size
            if (filesize($file1) !== filesize($file2)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Size of File is Not Equal!';

                return $this;
            }

            // Compare md5
            if (md5_file($file1) !== md5_file($file2)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Content of File is Not Equal!';

                return $this;
            }
        } catch (Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t Compare File : ' . $e->getMessage();
        }

        return $this;
    }

    // Method for get file extension
    public function extensionFile($path, $file_name, $extension)
    {
        $file = "{$path}/{$file_name}";

        $this->outputs['service'] = 'Check Extension File';
        $this->outputs['url']     = $file;

        if ($ext !== $this->getExtension($file)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Extension File Not Match';
        }

        return $this;
    }

    // Method for check remain file
    public function remainFile($path, $channel, $min)
    {
        $this->outputs['service'] = 'Check Remain File';
        $total_channel = count($channel);

        // Path channel
        for ($i = 0; $i < $total_channel; $i++) {
            $path_replace = str_replace('[service]', $channel[$i], $path);

            if ($i === 0) {
                $this->outputs['url'] = $path_replace;
            } else {
                $this->outputs['url'] .= '<br>' . $path_replace;
            }

            // Check directory exists
            if (!$this->pathFileExists($path_replace)) {
                if ($i === 0) {
                    $this->outputs['status'] = 'ERROR';
                    $this->outputs['remark'] = 'Directory Does Not Exists!';
                } else {
                    $this->outputs['status'] .= '<br>ERROR';
                    $this->outputs['remark'] .= '<br>Directory Does Not Exists!';
                }

                continue;
            }

            // Scan file in path
            $file = scandir($path_replace, 1);
            $total_file = count($file) - 2;
            
            $date_now = date("Y-m-d H:i:s");

            // Check File remain
            for ($j = 0; $j < $total_file; $j++) {
                $modify_date = date("Y-m-d H:i:s", filemtime($path_replace . '/' . $file[$j]));
                $diff_min    = $this->dateDifference($date_now, $modify_date);

                if ($diff_min > $min) {
                    if ($j === 0) {
                        $this->outputs['status'] = 'ERROR';
                        $this->outputs['remark'] = "File " . $file[$j] . " is remain!";
                    } else {
                        $this->outputs['status'] .= '<br>ERROR';
                        $this->outputs['remark'] .= "<br>File " . $file[$j] . " is remain!";
                    }
                }
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
        $handle = fopen($file, $mode);

        return $handle;
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

    public function __destruct()
    {
        parent::__destruct();
    }
}
