<?php
namespace EggDigital\HealthCheck\Classes;

use SplFileObject;

class File extends Base
{
    private $module = 'File';
    private $handle;
    private $file;
    private $outputs = [];
    
    public function __construct($file, $mode = 'rb')
    {
        $this->file = $file;
        $this->handle = $this->openFile($mode);
    }

    private function openFile($mode)
    {
        // First, see if the file exists
        if (!is_file($this->file)) {
            die('404 File not found!');
        }

        return new SplFileObject($this->file, $mode);
    }

    // Get contents of file
    public function readFile()
    {
        $status = [
            'topic'   => 'CheckReadFile',
            'module'  => $this->module,
            'success' => true,
            'desc'    => 'Read file success'
        ];

        $contents = $this->handle->fread($this->handle->getSize());

        if (!$contents) {
            $status = [
                'success' => false,
                'desc'    => 'Can not read file'
            ];
        }
        
        $this->outputs[] = $status;

        return $this->outputs;
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

    public function get($limit = null, $offset = 0)
    {
        if (empty($limit)) {
            return $this->all();
        }

        // Get datas
        $lines = [];
        $i = 0;
        while ($i < $limit) {
            $this->handle->seek($offset + $i);

            // Fix bug end of file get content
            if ($this->handle->eof()) {
                $line = $this->handle->current();
                if (!count($line) || empty($line)) {
                    break;
                }
                $lines[] = trim($line.PHP_EOL);
                return $lines;
            }

            $line = $this->handle->current();
            $lines[] = trim($line.PHP_EOL);
            ++$i;
        }

        return $lines;
    }

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
    public function compareFiles($file1, $file2)
    {
        // If empty not same
        if (empty($file1)) {
            return false;
        }
        
        foreach ($file1 as $file_name => $size) {
            if (!isset($file2[$file_name])) {
                // File lose
                return false;
            }

            if ($file2[$file_name] != $size) {
                // File size change
                return false;
            }
        }
        return true;
    }
}
