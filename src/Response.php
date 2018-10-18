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

class Response
{

    /**
     * @var string
     */
    protected $_httpVersion;

    /**
     * @var int
     */
    protected $_httpStatus;

    /**
     * @var string
     */
    protected $_httpReason;

    /**
     * @var array
     */
    protected $_headers;

    /**
     * @var string
     */
    protected $_textBody;

    /**
     * @var null|string
     */
    protected $_raw_headers;

    /**
     * @var null|string
     */
    protected $_raw_body;

    /**
     * Response constructor.
     * @param string $_raw_body
     * @param string|null $_raw_headers
     */
    public function __construct($_raw_body, $_raw_headers = null)
    {
        $this->_raw_body = $_raw_body;
        if ($_raw_headers !== null) $this->_raw_headers = $_raw_headers;
        $this->_parseHeaders();
        $this->_parseBody();
    }

    /**
     * @return int
     */
    public function getStatus ()
    {
        return $this->_httpStatus;
    }

    public function getBody ()
    {
        return $this->_textBody;
    }

    public function getHeaders ()
    {
        return $this->_headers;
    }

    public function getHeader ($key)
    {
        if (!array_key_exists($key, $this->_headers)) return null;
        return $this->_headers[$key];

    }

    private function _parseHeaders()
    {
        $_headers = explode("\r\n", $this->_raw_headers);
        $_status_line = array_shift($_headers);
        $status = explode(" ", $_status_line);
        if (array_key_exists(0, $status)) $this->_httpVersion = $status[0];
        if (array_key_exists(1, $status)) $this->_httpStatus = (int)$status[1];
        if (array_key_exists(2, $status)) $this->_httpReason = $status[2];

        foreach ($_headers as $_header_line) {
            list ($key, $value) = explode(':', $_header_line, 2);
            $this->_headers[ltrim($key)] = ltrim($value);
        }
    }

    private function _parseBody()
    {
        $this->_textBody = $this->_raw_body;
    }
}