<?php
/**
 * Simple cURL wrapper
 *
 * @author    Simon Rodin <master@genx.ru>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   1.0
 * @link      https://github.com/genxoft/curl
 *
 */


namespace genxoft\curl;


class File
{

    public $name;
    public $mime = "application/octet-stream";

    protected $_filePath;
    protected $_size;
    protected $_fileData;

    public function __construct()
    {

    }

    static function loadFile($filePath, $fileName = null, $mimeType = null)
    {
        $fileRealPath = realpath($filePath);
        if (!file_exists($fileRealPath))
            throw new \Exception("File '$fileRealPath' does not exists");

        $file = new static();
        $file->_filePath = $fileRealPath;
        $file->_fileData = @file_get_contents($file->_filePath);

        if ($mimeType !== null) {
            $file->mime = $mimeType;
        } else if (function_exists("mime_content_type")) {
            $file->mime = mime_content_type($file->_filePath);
        }

        if (empty($file->_fileData))
            throw new \Exception("Can't read file '{$file->_filePath}'");

        $file->_size = strlen($file->_fileData);
        $file->name = ($fileName !== null) ? $fileName : basename($fileRealPath);
        return $file;
    }

    public function getFileData() {
        return $this->_fileData;
    }
}