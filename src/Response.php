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

class Response
{

    /**
     * HTTP protocol version
     * @var string
     */
    protected $_httpVersion;

    /**
     * HTTP status code
     * @var int
     */
    protected $_httpStatus;

    /**
     * HTTP status reason message
     * @var string
     */
    protected $_httpReason;

    /**
     * Response headers
     * @var string[]
     */
    protected $_headers;

    /**
     * Response body
     * @var string
     */
    protected $_textBody;

    /**
     * Response raw header
     * @var null|string
     */
    protected $_raw_header;

    /**
     * Response raw body
     * @var null|string
     */
    protected $_raw_body;

    /**
     * Response constructor.
     * @param string $_raw_body raw body
     * @param string|null $_raw_header raw header
     */
    public function __construct($_raw_body, $_raw_header = null)
    {
        $this->_raw_body = $_raw_body;
        if ($_raw_header !== null) $this->_raw_header = $_raw_header;
        $this->_parseHeaders();
        $this->_parseBody();
    }

    /**
     * Get HTTP status numeric code (200, 404, 301, etc.)
     * @return int
     */
    public function getStatus ()
    {
        return $this->_httpStatus;
    }

    /**
     * Get HTTP status message
     * @return int
     */
    public function getStatusMessage ()
    {
        return $this->_httpReason;
    }

    /**
     * Returns true if HTTP status is 2**
     * @return boolean
     */
    public function isSuccess ()
    {
        return substr((string)$this->_httpStatus, 0, 1) === "2";
    }

    /**
     * Get raw body as string
     * @return string
     */
    public function getBody ()
    {
        return $this->_textBody;
    }

    /**
     * Get headers as array
     * @return array (name => value)
     */
    public function getHeaders ()
    {
        return $this->_headers;
    }

    /**
     * Get header by name
     * @param string $name header param name
     * @return string
     */
    public function getHeader ($name)
    {
        if (!array_key_exists($name, $this->_headers)) return null;
        return $this->_headers[$name];

    }

    /**
     * headers parser
     * @internal
     */
    private function _parseHeaders()
    {
        $_headers = explode("\r\n", $this->_raw_header);
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

    /**
     * body parser
     * @internal
     */
    private function _parseBody()
    {
        $this->_textBody = $this->_raw_body;
    }
}