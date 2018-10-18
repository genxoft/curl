<?php
/**
 * Simple cURL wrapper
 *
 * @author    Simon Rodin <master@genx.ru>
 * @license   http://opensource.org/licenses/MIT MIT Public
 * @version   0.1
 * @link      http://genx.ru
 *
 */

namespace genxter\curl;

class Request
{

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
     * @var array
     * files for upload
     */
    protected $_files;

    /**
     * @var array
     * default headers
     */
    public $defaultHeaders = [];

    /**
     * Request constructor.
     * @param string $url
     * @param array|null $params
     * @param string $method
     * @throws \Exception
     */
    public function __construct($url, $params = null, $method = 'GET')
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            throw new \InvalidArgumentException("Invalid url format. Url id: ".$url);
        $this->_url = $url;

        if ($params !== null) {
            if (!is_array($params))
                throw new \InvalidArgumentException("Invalid params type.");
            switch (strtoupper($method)) {
                case "GET":
                case "HEAD":
                    $this->addGetParams($params);
                    break;
                default:
                    $this->addPostParams($params);
                    break;
            }
        }

    }

    /**
     * @param string $url
     * @return Request
     */
    public function setUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            throw new \InvalidArgumentException("Invalid url format. Url id: ".$url);
        $this->_url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $key
     * @param string $val
     * @param bool $replace
     * @return Request
     * @throws \Exception
     */
    public function addGetParam($key, $val, $replace = false)
    {
        if (array_key_exists($key, $this->_getParams) && !$replace)
            throw new \Exception("Get param $key already exists");
        $this->_getParams[$key] = $val;
        return $this;
    }

    /**
     * @param array $params
     * @param bool $replace
     * @return Request
     * @throws \Exception
     */
    public function addGetParams($params, $replace = false)
    {
        foreach ($params as $key => $val)
            $this->addGetParam($key, $val, $replace);
        return $this;
    }

    /**
     * @param string $key
     * @return Request
     */
    public function removeGetParam($key)
    {
        if (array_key_exists($key, $this->_getParams)) unset($this->_getParams[$key]);
        return $this;
    }

    /**
     * @param string $key
     * @param string $val
     * @param bool $replace
     * @return Request
     * @throws \Exception
     */
    public function addPostParam($key, $val, $replace = false)
    {
        if (array_key_exists($key, $this->_postParams) && !$replace)
            throw new \Exception("Post param $key already exists");
        $this->_postParams[$key] = $val;
        return $this;
    }

    /**
     * @param array $params
     * @param bool $replace
     * @return Request
     * @throws \Exception
     */
    public function addPostParams($params, $replace = false)
    {
        foreach ($params as $key => $val)
            $this->addPostParam($key, $val, $replace);
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setJsonBody ($data)
    {
        $this->_body = json_encode($data);
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setRawBody ($data)
    {
        $this->_body = $data;
        return $this;
    }

    /**
     * @param string $key
     * @return Request
     */
    public function removePostParam($key)
    {
        if (array_key_exists($key, $this->_postParams)) unset($this->_postParams[$key]);
        return $this;
    }

    /**
     * @param string $key
     * @param string $val
     * @param bool $replace
     * @return Request
     * @throws \Exception
     */
    public function addHeader($key, $val, $replace = false)
    {
        if (array_key_exists($key, $this->_headers) && !$replace)
            throw new \Exception("Header $key already exists");
        $this->_headers[$key] = $val;
        return $this;
    }

    /**
     * @param array $params
     * @param bool $replace
     * @return Request
     * @throws \Exception
     */
    public function addHeaders($params, $replace = false)
    {
        foreach ($params as $key => $val)
            $this->addHeader($key, $val, $replace);
        return $this;
    }

    /**
     * @param string $key
     * @return Request
     */
    public function removeHeader($key)
    {
        if (array_key_exists($key, $this->_headers)) unset($this->_headers[$key]);
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

        if (!empty($this->_body))
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_body);
        else if (!empty($this->_postParams))
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->_postParams));

        if (!empty($this->_headers)) {
            $parsedHeader = [];
            foreach ($this->_headers as $key => $value) {
                array_push($parsedHeader, $key.': '.$value);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $parsedHeader);
        }

        curl_setopt($curl, CURLOPT_URL, $this->_url);
    }
}