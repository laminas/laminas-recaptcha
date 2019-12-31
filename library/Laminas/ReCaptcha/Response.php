<?php

/**
 * @see       https://github.com/laminas/laminas-recaptcha for the canonical source repository
 * @copyright https://github.com/laminas/laminas-recaptcha/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-recaptcha/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ReCaptcha;

use Laminas\Http\Response as HTTPResponse;

/**
 * Laminas_Service_ReCaptcha_Response
 *
 * @category   Laminas
 * @package    Laminas_Service
 * @subpackage ReCaptcha
 */
class Response
{
    /**
     * Status
     *
     * true if the response is valid or false otherwise
     *
     * @var boolean
     */
    protected $status = null;

    /**
     * Error code
     *
     * The error code if the status is false. The different error codes can be found in the
     * recaptcha API docs.
     *
     * @var string
     */
    protected $errorCode = null;

    /**
     * Class constructor used to construct a response
     *
     * @param string $status
     * @param string $errorCode
     * @param \Laminas\Http\Response $httpResponse If this is set the content will override $status and $errorCode
     */
    public function __construct($status = null, $errorCode = null, HTTPResponse $httpResponse = null)
    {
        if ($status !== null) {
            $this->setStatus($status);
        }

        if ($errorCode !== null) {
            $this->setErrorCode($errorCode);
        }

        if ($httpResponse !== null) {
            $this->setFromHttpResponse($httpResponse);
        }
    }

    /**
     * Set the status
     *
     * @param string $status
     * @return \Laminas\ReCaptcha\Response
     */
    public function setStatus($status)
    {
        if ($status === 'true') {
            $this->status = true;
        } else {
            $this->status = false;
        }

        return $this;
    }

    /**
     * Get the status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Alias for getStatus()
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->getStatus();
    }

    /**
     * Set the error code
     *
     * @param string $errorCode
     * @return \Laminas\ReCaptcha\Response
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * Get the error code
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Populate this instance based on a Laminas_Http_Response object
     *
     * @param \Laminas\Http\Response $response
     * @return \Laminas\ReCaptcha\Response
     */
    public function setFromHttpResponse(HTTPResponse $response)
    {
        $body = $response->getBody();

        $parts = explode("\n", $body, 2);

        if (count($parts) !== 2) {
            $status = 'false';
            $errorCode = '';
        } else {
            list($status, $errorCode) = $parts;
        }

        $this->setStatus($status);
        $this->setErrorCode($errorCode);

        return $this;
    }
}
