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

class Request
{

    const ENCTYPE_X_FORM = "application/x-www-form-urlencoded";
    const ENCTYPE_MULTIPART = "multipart/form-data";
    const ENCTYPE_TEXT = "text/plain";

    /**
     * @var string
     * Request URL
     */
    protected $_url;

    /**
     * @var array
     * Headers
     */
    protected $_headers = [];

    /**
     * @var array
     * GET params
     */
    protected $_getParams = [];

    /**
     * @var array
     * POST params
     */
    protected $_postParams = [];

    /**
     * @var string
     * body
     */
    protected $_body;

    /**
     * @var File[]
     * files for upload
     */
    protected $_files;

    /**
     * @var string
     * Request enctype
     */
    protected $_enctype = self::ENCTYPE_X_FORM;


    /**
     * @var string
     * Body parts delimiter
     */
    protected $_boundary = "-----------------------------0123456789";
    /**
     * Request constructor.
     * @param string $url
     * @param array|null $params
     * @param string $paramsType
     * @throws \InvalidArgumentException
     * @throws \Exception
     */

    public function __construct($url, $params = null, $paramsType = 'GET')
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            throw new \InvalidArgumentException("Invalid url format. Url id: ".$url);
        $this->_url = $url;

        if ($params !== null) {
            if (!is_array($params))
                throw new \InvalidArgumentException("Invalid params type.");
            switch (strtoupper($paramsType)) {
                case "GET":
                case "HEAD":
                    $this->addGetParams($params);
                    break;
                case "JSON":
                    $this->setJsonBody($params);
                    break;
                case "POST":
                default:
                    $this->addPostParams($params);
                    break;
            }
        }
        $this->_boundary = '-----------------------------215672773019479';
        //$this->_boundary = '-----------------------------'.mt_rand(pow(10, 9), pow(10, 10) - 1);

    }

    /**
     * Set URL string
     * @param string $url
     * @return static
     */
    public function setUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            throw new \InvalidArgumentException("Invalid url format. Url id: ".$url);
        $this->_url = $url;
        return $this;
    }

    /**
     * Get URL string
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Add param to request header
     * @param string $name param name
     * @param string $val param value
     * @param bool $replace if true replace existing param then exception
     * @return static
     * @throws \Exception
     */
    public function addHeader($name, $val, $replace = false)
    {
        if (array_key_exists($name, $this->_headers) && !$replace)
            throw new \Exception("Header $name already exists");
        $this->_headers[$name] = $val;
        return $this;
    }

    /**
     * Add params array to request header
     * @param array $params params (name => value)
     * @param bool $replace if true replace existing param then exception
     * @return static
     * @throws \Exception
     */
    public function addHeaders($params, $replace = false)
    {
        foreach ($params as $name => $val)
            $this->addHeader($name, $val, $replace);
        return $this;
    }

    /**
     * Remove param from request header
     * @param string $name param name
     * @return static
     */
    public function removeHeader($name)
    {
        if (array_key_exists($name, $this->_headers)) unset($this->_headers[$name]);
        return $this;
    }

    /**
     * Add param to request query
     * @param string $name param name
     * @param string $val param value
     * @param bool $replace if true replace existing param then exception
     * @return static
     * @throws \Exception
     */
    public function addGetParam($name, $val, $replace = false)
    {
        if (array_key_exists($name, $this->_getParams) && !$replace)
            throw new \Exception("Get param $name already exists");
        $this->_getParams[$name] = $val;
        return $this;
    }

    /**
     * Add params array to request query
     * @param array $params params (name => value)
     * @param bool $replace if true replace existing param then exception
     * @return static
     * @throws \Exception
     */
    public function addGetParams($params, $replace = false)
    {
        foreach ($params as $name => $val)
            $this->addGetParam($name, $val, $replace);
        return $this;
    }

    /**
     * Remove param from request query
     * @param string $name param name
     * @return static
     */
    public function removeGetParam($name)
    {
        if (array_key_exists($name, $this->_getParams)) unset($this->_getParams[$name]);
        return $this;
    }

    /**
     * Add param to request body
     * @param string $name param name
     * @param string $val param value
     * @param bool $replace if true replace existing param then exception
     * @return static
     * @throws \Exception
     */
    public function addPostParam($name, $val, $replace = false)
    {
        if (array_key_exists($name, $this->_postParams) && !$replace)
            throw new \Exception("Post param $name already exists");
        if ($val instanceof File) {
            $this->_files[$name] = $val;
        } else {
            $this->_postParams[$name] = $val;
        }
        return $this;
    }

    /**
     * Add params array to request body
     * @param array $params params (name => value)
     * @param bool $replace if true replace existing param then exception
     * @return static
     * @throws \Exception
     */
    public function addPostParams($params, $replace = false)
    {
        foreach ($params as $name => $val)
            $this->addPostParam($name, $val, $replace);
        return $this;
    }

    /**
     * Remove param from request body
     * @param string $name param name
     * @return static
     */
    public function removePostParam($name)
    {
        if (array_key_exists($name, $this->_postParams)) unset($this->_postParams[$name]);
        return $this;
    }

    /**
     * Set json to request body
     * @param mixed $data can be any type except a resource.
     * @param int $jsonEncodeOptions json_encode options
     * @param int $jsonEncodeDepth json_encode depth
     * @return static
     */
    public function setJsonBody ($data, $jsonEncodeOptions = 0, $jsonEncodeDepth = 512)
    {
        $this->_body = json_encode($data, $jsonEncodeOptions, $jsonEncodeDepth);
        return $this;
    }

    /**
     * Set raw request body
     * @param string $data raw body data
     * @return static
     */
    public function setRawBody ($data)
    {
        $this->_body = $data;
        return $this;
    }

    /**
     * @param resource $curl
     */
    public function buildRequest(&$curl)
    {
        if (!empty($this->_getParams)) {
            if (strpos($this->_url, '?') !== false) {
                $this->_url = $this->_url.'&'.http_build_query($this->_getParams);
            } else {
                $this->_url = $this->_url.'?'.http_build_query($this->_getParams);
            }
        }

        $this->_encryptBody();

        if (!empty($this->_body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_body);
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($this->_enctype === self::ENCTYPE_MULTIPART) {
                $this->addHeaders([
                    'Content-Type' => 'multipart/form-data; boundary=' . $this->_boundary,
                    'Content-Length' => strlen($this->_body),
                ], true);
            }
        }

        if (!empty($this->_headers)) {
            $parsedHeader = [];
            foreach ($this->_headers as $name => $value) {
                array_push($parsedHeader, $name.': '.$value);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $parsedHeader);
        }

        curl_setopt($curl, CURLOPT_URL, $this->_url);
    }

    /**
     * Prepare body for POST request
     * @internal
     */
    protected function _encryptBody()
    {
        if (!empty($this->_body)) return;

        if (!empty($this->_postParams) && empty($this->_files)) {
            $this->_body = http_build_query($this->_postParams);
            $this->_enctype = self::ENCTYPE_X_FORM;
            return;
        }

        if (!empty($this->_files)) {
            $this->_enctype = self::ENCTYPE_MULTIPART;
            foreach ($this->_postParams as $name => $val) {
                $this->_body .= static::_getPart($name, $val, $this->_boundary);
            }
            foreach ($this->_files as $name => $file) {
                $this->_body .= static::_getPart($name, $file, $this->_boundary);
            }
            $this->_body .= '--'.$this->_boundary."--\r\n";
        }
    }

    protected static function _getPart($name, $value, $boundary)
    {
        $part = '--'.$boundary."\r\n";
        if ($value instanceof File) {
            $part .= "Content-Disposition: form-data; name=\"$name\"; filename=\"{$value->name}\"\r\n";
            $part .= "Content-Type: {$value->mime}";
            $part .= "\r\n\r\n";
            $part .= $value->getFileData();
            $part .= "\r\n";
        } else {
            $part .= "Content-Disposition: form-data; name=\"$name\"";
            $part .= "\r\n\r\n";
            $part .= urlencode($value);
            $part .= "\r\n";
        }
        return $part;
    }
}