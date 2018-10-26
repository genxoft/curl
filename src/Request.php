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

namespace genxoft\curl;

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
        $this->_postParams[$name] = $val;
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

        if (!empty($this->_body))
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_body);
        else if (!empty($this->_postParams))
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->_postParams));

        if (!empty($this->_headers)) {
            $parsedHeader = [];
            foreach ($this->_headers as $name => $value) {
                array_push($parsedHeader, $name.': '.$value);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $parsedHeader);
        }

        curl_setopt($curl, CURLOPT_URL, $this->_url);
    }
}