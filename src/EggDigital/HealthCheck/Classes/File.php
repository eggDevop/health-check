<?php
namespace EggDigital\HealthCheck\Classes;

class File extends Base
{
    private $module = 'File';
    private $handle;
    private $file;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->outputs['module'] = 'File';
    }

    // Method for check path file
    private function pathFileExists($path)
    {
        if (!is_dir($path)) {
            return false;
        }

        return true;
    }

    private function fileExists($file)
    {
        if (!is_file($file)) {
            return false;
        }

        return true;
    }

    // Method for open file
    private function openFile($file, $mode)
    {
        $file_handle = fopen($file, $mode);

        return $file_handle;
    }

    // Get contents of file
    public function readFile($path, $file_name)
    {
        $this->outputs['service'] = 'Check Read File';
        $this->outputs['url']     = $path;

        try {
            $file = $path . '/' . $file_name;

            if (!$this->pathFileExists($path)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Path not found!';

                return $this;
            }

            if (!$this->fileExists($file)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = '404 File not found!';

                return $this;
            }

            $file_handle = $this->openFile($file, 'r');

            $contents = fread($file_handle, filesize($file));

            if (!$contents) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t read file';
            }

            fclose($file_handle);

            return $this;
        } catch(Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t read file : ' . $e->getMessage();
        }
    }

    public function deleteFile()
    {
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

    // public function getData($limit = null, $offset = 0)
    // {
    //     if (empty($limit)) {
    //         return $this->all();
    //     }

    //     // Get datas
    //     $lines = [];
    //     $i = 0;
    //     while ($i < $limit) {
    //         $this->handle->seek($offset + $i);

    //         // Fix bug end of file get content
    //         if ($this->handle->eof()) {
    //             $line = $this->handle->current();
    //             if (!count($line) || empty($line)) {
    //                 break;
    //             }
    //             $lines[] = trim($line.PHP_EOL);
    //             return $lines;
    //         }

    //         $line = $this->handle->current();
    //         $lines[] = trim($line.PHP_EOL);
    //         ++$i;
    //     }

    //     return $lines;
    // }

    public function all()
    {
        $lines = [];
        while (!$this->handle->eof()) {
            $lines[] = $this->handle->fgets();
        }

        return $lines;
    }

    public function getExtension()
    {
        return $this->handle->getExtension();
    }
    
    public function getLastLine($file)
    {
        $line   = '';
        $handle = $this->openFile($file, 'rb');
        $cursor = -1;

        fseek($handle, $cursor, SEEK_END);
        $char = fgetc($handle);

        // Trim trailing newline chars of the file
        while ($char === "\n" || $char === "\r") {
            fseek($handle, $cursor--, SEEK_END);
            $char = fgetc($handle);
        }

        // Read until the start of file or first newline char
        while ($char !== false && $char !== "\n" && $char !== "\r") {
            // Prepend the new char
            $line = $char . $line;
            fseek($handle, --$cursor, SEEK_END);
            $char = fgetc($handle);
        }

        $this->closeFile($handle);

        return $line;
    }

    // Method for get files size in folder
    protected function getFilesAndSizeInFolder($folder)
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

            $outputs[$file] = filesize("{$folder}{$file}");
        }

        return $outputs;
    }

    // Method for compare file
    public function compareFiles($path1, $path2, $file_name1, $file_name2)
    {
        $this->outputs['service'] = 'Check Compare File';
        $this->outputs['url']     = 'Path 1 : ' . $path1 . ', Path 2 : ' . $path2;

        if (!$this->pathFileExists($path1)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Path 1 not found!';

            return $this;
        }

        if (!$this->pathFileExists($path2)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Path 2 not found!';

            return $this;
        }

        $file1 = $path1 . '/' . $file_name1;
        $file2 = $path2 . '/' . $file_name2;

        if (!$this->fileExists($file1)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = '404 File 1 not found!';

            return $this;
        }

        if (!$this->fileExists($file2)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = '404 File 2 not found!';

            return $this;
        }

        try {
            if (filesize($file1) !== filesize($file2)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Size of file is not equal!';

                return $this;
            }

            if (md5_file($file1) !== md5_file($file2)) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Content of file is not equal!';

                return $this;
            }

            return $this;
        } catch(Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t compare file : ' . $e->getMessage();
        }
    }

    // Method for write file
    public function writeFile($path)
    {
        $this->outputs['service'] = 'Check Write File';
        $this->outputs['url']     = $path;

        if (!$this->pathFileExists($path)) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Path not found!';

            return $this;
        }

        $file_name = 'TEST_' . date('Y-m-d') . '.txt';
        $file = $path . '/' . $file_name;
        $file_handle = $this->openFile($file, 'a');
        
        try {
            $written = fwrite($file_handle, "12345");

            if (!$written) {
                $this->outputs['status']  = 'ERROR';
                $this->outputs['remark']  = 'Can\'t write file';
            }

            fclose($file_handle);

            return $this;
        } catch(Exception $e) {
            $this->outputs['status']  = 'ERROR';
            $this->outputs['remark']  = 'Can\'t write file : ' . $e->getMessage();
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
