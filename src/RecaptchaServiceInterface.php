<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha;

/**
 * An interface for interacting with a recaptcha service provider
 */
interface RecaptchaServiceInterface
{
    /**
     * Get the options array
     *
     * @return array<string, mixed>
     */
    public function getOptions();

    /**
     * Set a single option
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setOption($key, $value);

    /**
     * Get the parameter array
     *
     * @return array<string, mixed>
     */
    public function getParams();

    /**
     * Set a single parameter
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParam($key, $value);

    /**
     * Get the site key
     *
     * @return string
     */
    public function getSiteKey();

    /**
     * Set the site key
     *
     * @param string $siteKey
     * @return $this
     */
    public function setSiteKey($siteKey);

    /**
     * Get the secret key
     *
     * @return string
     */
    public function getSecretKey();

    /**
     * Set the secret key
     *
     * @param string $secretKey
     * @return $this
     */
    public function setSecretKey($secretKey);

    /**
     * Verify the user input
     *
     * @param string $responseField
     * @return Response
     */
    public function verify($responseField);
}
