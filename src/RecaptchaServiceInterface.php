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
    public function getOptions(): array;

    /**
     * Set a single option
     */
    public function setOption(string $key, mixed $value): self;

    /**
     * Get the parameter array
     *
     * @return array<string, mixed>
     */
    public function getParams(): array;

    /**
     * Set a single parameter
     */
    public function setParam(string $key, mixed $value): self;

    /**
     * Get the site key
     */
    public function getSiteKey(): string;

    /**
     * Set the site key
     */
    public function setSiteKey(string $siteKey): self;

    /**
     * Get the secret key
     */
    public function getSecretKey(): string;

    /**
     * Set the secret key
     */
    public function setSecretKey(string $secretKey): self;

    /**
     * Verify the user input
     */
    public function verify(string $responseField): Response;
}
