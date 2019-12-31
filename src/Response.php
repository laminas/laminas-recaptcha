<?php

/**
 * @see       https://github.com/laminas/laminas-recaptcha for the canonical source repository
 * @copyright https://github.com/laminas/laminas-recaptcha/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-recaptcha/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ReCaptcha;

use Laminas\Http\Response as HTTPResponse;

/**
 * Model responses from the ReCaptcha and Mailhide APIs.
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
     * Error codes
     *
     * The error codes if the status is false. The different error codes can be found in the
     * recaptcha API docs.
     *
     * @var array
     */
    protected $errorCodes = [];

    /**
     * Class constructor used to construct a response
     *
     * @param string $status
     * @param array $errorCodes
     * @param HTTPResponse $httpResponse If this is set the content will override $status and $errorCode
     */
    public function __construct($status = null, $errorCodes = [], HTTPResponse $httpResponse = null)
    {
        if ($status !== null) {
            $this->setStatus($status);
        }

        if (! empty($errorCodes)) {
            $this->setErrorCodes($errorCodes);
        }

        if ($httpResponse !== null) {
            $this->setFromHttpResponse($httpResponse);
        }
    }

    /**
     * Set the status
     *
     * @param bool $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = (bool) $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Alias for getStatus()
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->getStatus();
    }

    /**
     * Set the error codes
     *
     * @param array $errorCodes
     * @return self
     */
    public function setErrorCodes($errorCodes)
    {
        if (is_string($errorCodes)) {
            $errorCodes = [$errorCodes];
        }

        $this->errorCodes = $errorCodes;

        return $this;
    }

    /**
     * Get the error codes
     *
     * @return array
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }

    /**
     * Populate this instance based on a Laminas_Http_Response object
     *
     * @param HTTPResponse $response
     * @return self
     */
    public function setFromHttpResponse(HTTPResponse $response)
    {
        $body = $response->getBody();

        $parts = json_decode($body, true);

        $status = false;
        $errorCodes = [];

        if (is_array($parts) && array_key_exists('success', $parts)) {
            $status = $parts['success'];
            if (array_key_exists('error-codes', $parts)) {
                $errorCodes = $parts['error-codes'];
            }
        }

        $this->setStatus($status);
        $this->setErrorCodes($errorCodes);

        return $this;
    }
}
