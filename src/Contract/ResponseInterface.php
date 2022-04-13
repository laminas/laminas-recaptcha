<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha\Contract;

use Laminas\Http\Response as HTTPResponse;

interface ResponseInterface
{
    /** @return string[] */
    public function getErrorCodes(): array;

    public function getStatus(): bool;

    public function isValid(): bool;

    /** @param string|string[] $errorCodes */
    public function setErrorCodes($errorCodes): self;

    public function setFromHttpResponse(HTTPResponse $httpResponse): self;

    public function setStatus(bool $status): self;
}
