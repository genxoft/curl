<?php
/**
 * Simple cURL wrapper
 *
 * @author    Simon Rodin <master@genx.ru>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @link      https://github.com/genxoft/curl
 *
 */


namespace genxoft\curl;

class File
{

    /**
     * File name
     * @var string
     */
    public $name;

    /**
     * File MIME type
     * @var string
     */
    public $mime = "application/octet-stream";

    /**
     * File path
     * @var
     */
    protected $_filePath;

    /**
     * File size
     * @var
     */
    protected $_size;

    /**
     * File content
     * @var
     */
    protected $_fileData;

    /**
     * File constructor.
     */
    public function __construct()
    {

    }

    /**
     * Load file from filesystem
     * @param string $filePath path to file
     * @param string|null $fileName file name or NULL if you want to use original file name
     * @param string|null $mimeType file MIME type or NULL if you want to use autodetect MIME type
     * @return static
     * @throws \Exception
     */
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

    /**
     * File content getter
     * @return string
     */
    public function getFileData() {
        return $this->_fileData;
    }
}