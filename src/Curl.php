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


class Curl
{
    /**
     * @var Request
     * request
     */
    protected $_request;

    /**
     * @var array
     * Default options php-curl
     */
    protected $_options = [
        CURLOPT_USERAGENT       => 'genxoft-php-curl-wrapper',
        CURLOPT_TIMEOUT         => 60,
        CURLOPT_CONNECTTIMEOUT   => 60,
        CURLOPT_RETURNTRANSFER   => true,
        CURLOPT_HEADER          => true,
    ];

    /**
     * @var int
     * Last php-curl error number
     */
    protected $_lastCurlError;

    /**
     * @var string
     * Last php-curl error message
     */
    protected $_lastCurlErrorStr;

    /**
     * Curl constructor.
     * @param Request|string|null $request Request object, URL string
     * @throws \Exception
     */
    public function __construct($request = null)
    {
        if ($request !== null) {
            if ($request instanceof Request) {
                $this->_request = $request;
            } else if (is_string($request)) {
                $this->_request = new Request($request);
            }
        }
    }

    /**
     * Set php-curl option
     * @param int $key
     * @param mixed $val
     */
    public function setCurlOption ($key, $val) {
        $this->_options[$key] = $val;
    }

    /**
     * Unset php-curl option
     * @param int $key
     */
    public function unsetCurlOption ($key) {
        if (isset($this->_options[$key])) unset($this->_options[$key]);
    }

    /**
     * Set Request object
     * @param Request $request
     */
    public function setRequest ($request) {
        if ($request instanceof Request)
            throw new \InvalidArgumentException("Invalid request type.");
        $this->_request = $request;
    }

    /**
     * Get Request object
     * @return Request
     */
    public function getRequest () {
        return $this->_request;
    }

    /**
     * Get last php-curl error message
     * @return string
     */
    public function getLastError () {
        return $this->_lastCurlErrorStr;
    }

    /**
     * Get last php-curl error number
     * @return int
     */
    public function getLastErrno () {
        return $this->_lastCurlError;
    }

    /**
     * Performing curl request with method GET
     * @return Response
     * @throws \Exception
     */
    public function get()
    {
        $result = $this->_doRequest();
        return $result;
    }

    /**
     * Performing curl request with method POST
     * @return Response
     * @throws \Exception
     */
    public function post()
    {
        $result = $this->_doRequest('POST');
        return $result;
    }

    /**
     * Performing curl request with method HEAD
     * @return Response
     * @throws \Exception
     */
    public function head()
    {
        $result = $this->_doRequest('HEAD');
        return $result;
    }

    /**
     * Performing curl request with method PUT
     * @return Response
     * @throws \Exception
     */
    public function put()
    {
        $result = $this->_doRequest('PUT');
        return $result;
    }

    /**
     * Performing curl request with method PATCH
     * @return Response
     * @throws \Exception
     */
    public function patch()
    {
        $result = $this->_doRequest('PATCH');
        return $result;
    }

    /**
     * Performing curl request with method DELETE
     * @return Response
     * @throws \Exception
     */
    public function delete()
    {
        $result = $this->_doRequest('DELETE');
        return $result;
    }

    /**
     * Performing curl request
     * @param string $method HTTP method (GET, POST, HEAD, PUT, PATCH, DELETE)
     * @return Response|null Returns Response object. Returns null if curl error
     * @throws \Exception
     * @internal
     */
    protected function _doRequest($method = 'GET')
    {
        if ($this->_request === null)
            throw new \Exception("Request is required.");

        if (!($this->_request instanceof Request))
            throw new \Exception("Invalid request type.");


        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        if ($method === 'HEAD') {
            curl_setopt($curl, CURLOPT_NOBODY, true);
        }

        $this->_request->buildRequest($curl);

        curl_setopt_array($curl, $this->_options);
        $response = curl_exec($curl);

        if ($response === false) {
            $this->_lastCurlError = curl_errno($curl);
            $this->_lastCurlErrorStr = curl_strerror($this->_lastCurlError);
            return null;
        }

        if (array_key_exists(CURLOPT_HEADER, $this->_options) && $this->_options[CURLOPT_HEADER]) {
            $responseHeaders = substr($response, 0, strpos($response, "\r\n\r\n"));
            $responseBody = substr($response, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
        } else {
            $responseHeaders = null;
            $responseBody = $response;
        }

        curl_close($curl);
        return new Response($responseBody, $responseHeaders);
    }

    /**
     * Performing quick http request with method GET
     * @param string $url URL string
     * @param array $params HTTP query params (name => value)
     * @return string
     * @throws \Exception
     */
    static function quickGet($url, $params)
    {
        $curl = new static(new Request($url, $params));
        $response = $curl->get();
        if ($response === null)
            throw new \Exception("Curl error: ". $curl->getLastError());

        if (!$response->isSuccess())
            throw new \Exception("HTTP Error: ". $response->getStatusMessage());

        return $response->getBody();
    }

    /**
     * Performing quick http request with method POST
     * @param string $url URL string
     * @param array $params HTTP body params (name => value)
     * @return string
     * @throws \Exception
     */
    static function quickPost($url, $params)
    {
        $curl = new static(new Request($url, $params, 'POST'));
        $response = $curl->post();
        if ($response === null)
            throw new \Exception("Curl error: ". $curl->getLastError());

        if (!$response->isSuccess())
            throw new \Exception("HTTP Error: ". $response->getStatusMessage());

        return $response->getBody();
    }

    /**
     * Performing quick http request with method POST and params in body with json encoding
     * @param string $url URL string
     * @param array $params Json params (name => value)
     * @return string
     * @throws \Exception
     */
    static function quickJson($url, $params)
    {
        $curl = new static(new Request($url, $params, 'JSON'));
        $response = $curl->post();
        if ($response === null)
            throw new \Exception("Curl error: ". $curl->getLastError());

        if (!$response->isSuccess())
            throw new \Exception("HTTP Error: ". $response->getStatusMessage());

        return $response->getBody();
    }
}