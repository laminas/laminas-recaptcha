<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha;

use JsonException;
use Laminas\Http\Response as HTTPResponse;
use Laminas\ReCaptcha\Contract\ResponseInterface;
use Throwable;

use function array_key_exists;
use function gettype;
use function is_array;
use function is_string;
use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * Model responses from the ReCaptcha and MailHide APIs.
 */
final class Response implements ResponseInterface
{
    /**
     * @var bool
     */
    public const VALID = true;

    /**
     * @var bool
     */
    public const INVALID = false;

    /**
     * Status
     *
     * true if the response is valid.
     */
    private bool $status;

    /**
     * Error codes
     *
     * The error codes if the status is false. The different error codes can be found in the
     * recaptcha API docs.
     *
     * @var array<string>
     */
    private array $errorCodes = [];

    /**
     * Class constructor used to construct a response
     *
     * @param HTTPResponse|null $httpResponse If this is set the content will override $status and $errorCode
     * @throws Throwable
     */
    public function __construct(?bool $status = null, ?array $errorCodes = null, ?HTTPResponse $httpResponse = null)
    {
        if ($httpResponse instanceof HTTPResponse) {
            $this->setFromHttpResponse($httpResponse);
            return;
        }

        if ($status !== null) {
            $this->setStatus($status);
        }

        if ($errorCodes !== null) {
            $this->setErrorCodes($errorCodes);
        }
    }

    /** Set the status */
    public function setStatus(bool $status): ResponseInterface
    {
        $this->status = $status;

        return $this;
    }

    /** Get the status */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /** Alias for getStatus() */
    public function isValid(): bool
    {
        return $this->status === self::VALID;
    }

    /**
     * Set the error codes
     *
     * @param string|string[] $errorCodes
     * @throws Exception
     */
    public function setErrorCodes($errorCodes): ResponseInterface
    {
        if (is_string($errorCodes)) {
            $errorCodes = [$errorCodes];
        }

        if (is_array($errorCodes)) {
            $this->errorCodes = $errorCodes;
            return $this;
        }

        throw new Exception(sprintf(
            '%s expects an array or string $errorCodes; received "%s"',
            __METHOD__,
            gettype($errorCodes)
        ));
    }

    /**
     * Get the error codes
     *
     * @return string[]
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    /**
     * Populate this instance based on a Laminas\Http\Response object
     *
     * @throws JsonException|Exception
     */
    public function setFromHttpResponse(HTTPResponse $httpResponse): ResponseInterface
    {
        $status     = false;
        $errorCodes = [];
        $parts      = json_decode($httpResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (is_array($parts) && array_key_exists('success', $parts)) {
            $status = (bool) $parts['success'];

            if (array_key_exists('error-codes', $parts)) {
                $errorCodes = $parts['error-codes'] ?? [];
            }
        }

        $this->setStatus($status);
        $this->setErrorCodes($errorCodes);

        return $this;
    }
}
