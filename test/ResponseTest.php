<?php

declare(strict_types=1);

namespace LaminasTest\ReCaptcha;

use Laminas\Http\Response;
use Laminas\ReCaptcha;
use PHPUnit\Framework\TestCase;

use function json_encode;

class ResponseTest extends TestCase
{
    /** @var ReCaptcha\Response */
    protected $response;

    protected function setUp(): void
    {
        $this->response = new ReCaptcha\Response();
    }

    public function testSetAndGet(): void
    {
        /* Set and get status */
        $status = true;
        $this->response->setStatus($status);
        $this->assertTrue($this->response->getStatus());

        $status = false;
        $this->response->setStatus($status);
        $this->assertFalse($this->response->getStatus());

        /* Set and get the error codes */
        $errorCodes = 'foobar';
        $this->response->setErrorCodes($errorCodes);
        $this->assertSame([$errorCodes], $this->response->getErrorCodes());

        $errorCodes = ['foo', 'bar'];
        $this->response->setErrorCodes($errorCodes);
        $this->assertSame($errorCodes, $this->response->getErrorCodes());
    }

    public function testIsValid(): void
    {
        $this->response->setStatus(true);
        $this->assertTrue($this->response->isValid());
    }

    public function testIsInvalid(): void
    {
        $this->response->setStatus(false);
        $this->assertFalse($this->response->isValid());
    }

    public function testSetFromHttpResponse(): void
    {
        $status       = false;
        $errorCodes   = ['foo', 'bar'];
        $responseBody = json_encode([
            'success'     => $status,
            'error-codes' => $errorCodes,
        ]);
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $this->response->setFromHttpResponse($httpResponse);

        $this->assertFalse($this->response->getStatus());
        $this->assertSame($errorCodes, $this->response->getErrorCodes());
    }

    public function testConstructor(): void
    {
        $status     = true;
        $errorCodes = ['ok'];

        $response = new ReCaptcha\Response($status, $errorCodes);

        $this->assertTrue($response->getStatus());
        $this->assertSame($errorCodes, $response->getErrorCodes());
    }

    public function testConstructorWithHttpResponse(): void
    {
        $status       = false;
        $errorCodes   = ['foobar'];
        $responseBody = json_encode([
            'success'     => $status,
            'error-codes' => $errorCodes,
        ]);
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $response = new ReCaptcha\Response(null, null, $httpResponse);

        $this->assertFalse($response->getStatus());
        $this->assertSame($errorCodes, $response->getErrorCodes());
    }
}
