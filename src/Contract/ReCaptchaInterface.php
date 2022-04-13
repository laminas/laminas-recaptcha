<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha\Contract;

use Laminas\Http\Client as HttpClient;

interface ReCaptchaInterface
{
    public function setHttpClient(HttpClient $httpClient): self;

    public function getHttpClient(): HttpClient;

    public function __toString(): string;

    public function setIp(?string $ip): self;

    public function getIp(): ?string;

    public function getHtml(): string;

    /** @param mixed $value */
    public function setParam(string $key, $value): self;

    /** @param iterable<string, mixed> $params */
    public function setParams(iterable $params): self;

    /** @return array<string,mixed> */
    public function getParams(): array;

    /** @return mixed */
    public function getParam(string $key);

    /** @param mixed $value */
    public function setOption(string $key, string $value): self;

    public function setOptions(iterable $options): self;

    /** @return array<string,mixed> */
    public function getOptions(): array;

    /** @return mixed */
    public function getOption(string $key);

    public function getSiteKey(): ?string;

    public function setSiteKey(string $siteKey): self;

    public function getSecretKey(): ?string;

    public function setSecretKey(string $secretKey): self;

    public function verify(string $responseField): ResponseInterface;
}
