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


class Curl
{
    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_options = [
        CURLOPT_USERAGENT       => 'genxter-php-curl',
        CURLOPT_TIMEOUT         => 60,
        CURLOPT_CONNECTTIMEOUT   => 60,
        CURLOPT_RETURNTRANSFER   => true,
        CURLOPT_HEADER          => true,
    ];

    /**
     * @var int
     */
    protected $_lastCurlError;

    /**
     * @var string
     */
    protected $_lastCurlErrorStr;

    /**
     * Curl constructor.
     * @param Request|string|null $request
     */
    public function __construct($request = null)
    {
        if ($request !== null) {
            if ($request instanceof Request) {
                $this->_request = $request;
            } else if (is_string($request)) {
                $this->_request = new Request($request);
            } else {
                throw new \InvalidArgumentException("Invalid argument type.");
            }
        }
    }

    /**
     * @param int $key
     * @param mixed $val
     */
    public function setCurlOption ($key, $val) {
        $this->_options[$key] = $val;
    }

    /**
     * @param int $key
     */
    public function unsetCurlOption ($key) {
        if (isset($this->_options[$key])) unset($this->_options[$key]);
    }

    /**
     * @param Request $request
     */
    public function setRequest ($request) {
        if ($request instanceof Request) {
            throw new \InvalidArgumentException("Invalid request type.");
        }
    }

    /**
     * @return Request
     */
    public function getRequest () {
        return $this->_request;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function get()
    {
        $result = $this->_doRequest();
        return $result;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function post()
    {
        $result = $this->_doRequest('POST');
        return $result;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function head()
    {
        $result = $this->_doRequest('HEAD');
        return $result;
    }

    /**
     * @param string $method
     * @return Response|null
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

}