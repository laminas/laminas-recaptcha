<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha;

use Laminas\Http\Response as HTTPResponse;

use function array_key_exists;
use function is_array;
use function is_string;
use function json_decode;
use function trim;

use const JSON_THROW_ON_ERROR;

/**
 * Model responses from the ReCaptcha and MailHide APIs.
 */
class Response
{
    /**
     * Class constructor used to construct a response
     *
     * @param ?bool $status returns true if the response is valid or false otherwise
     * @param ?array $errorCodes The error codes if the status is false.
     *                           The different error codes can be found in the recaptcha API docs.
     * @param ?HTTPResponse $httpResponse If this is set the content will override $status and $errorCode
     */
    public function __construct(
        private ?bool $status = null,
        private ?array $errorCodes = null,
        private ?HTTPResponse $httpResponse = null
    ) {
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
     */
    public function setStatus(bool $status): self
    {
        $this->status = (bool) $status;

        return $this;
    }

    /**
     * Get the status
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * Alias for getStatus()
     */
    public function isValid(): bool
    {
        return $this->getStatus();
    }

    /**
     * Set the error codes
     *
     * @param mixed[] $errorCodes
     */
    public function setErrorCodes(string|array $errorCodes): self
    {
        if (is_string($errorCodes)) {
            $errorCodes = [$errorCodes];
        }

        $this->errorCodes = $errorCodes;

        return $this;
    }

    /**
     * Get the error codes
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    /**
     * Populate this instance based on a Laminas_Http_Response object
     */
    public function setFromHttpResponse(HTTPResponse $response): self
    {
        $body = $response->getBody();

        $parts = '' !== trim($body) ? json_decode($body, true, 512, JSON_THROW_ON_ERROR) : [];

        $status     = false;
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
