<?php

declare(strict_types=1);

namespace LaminasTest\ReCaptcha;

use Laminas\Http\Response;
use Laminas\ReCaptcha;
use Laminas\ReCaptcha\Contract\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Throwable;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final class ResponseTest extends TestCase
{
    private ResponseInterface $response;

    protected function setUp(): void
    {
        $this->response = new ReCaptcha\Response();
    }

    protected function validResponsesProvider(): iterable
    {
        yield 'true' => [['status' => true, 'errorCodes' => ['ok']]];
        yield 'false-single' => [['status' => false, 'errorCodes' => ['invalid-key']]];
        yield 'false-multiple' => [['status' => false, 'errorCodes' => ['invalid-response', 'invalid-key']]];
    }

    /**
     * @dataProvider validResponsesProvider
     * @covers \Laminas\ReCaptcha\Response::__construct
     * @covers \Laminas\ReCaptcha\Response::getErrorCodes
     * @covers \Laminas\ReCaptcha\Response::getStatus
     * @covers \Laminas\ReCaptcha\Response::setErrorCodes
     * @covers \Laminas\ReCaptcha\Response::setStatus
     * @throws ReCaptcha\Exception
     */
    public function testSetAndGet(array $response): void
    {
        /* Set and get status */
        $this->response->setStatus($response['status']);
        $this->assertSame($response['status'], $this->response->getStatus());

        /* Set and get the error codes */
        $this->response->setErrorCodes($response['errorCodes']);
        $this->assertSame($response['errorCodes'], $this->response->getErrorCodes());
    }

    /**
     * @covers \Laminas\ReCaptcha\Response::__construct
     * @covers \Laminas\ReCaptcha\Response::isValid
     * @covers \Laminas\ReCaptcha\Response::setStatus
     */
    public function testIsValid(): void
    {
        $this->response->setStatus(true);
        $this->assertTrue($this->response->isValid());
    }

    /**
     * @covers \Laminas\ReCaptcha\Response::__construct
     * @covers \Laminas\ReCaptcha\Response::isValid
     * @covers \Laminas\ReCaptcha\Response::setStatus
     */
    public function testIsInvalid(): void
    {
        $this->response->setStatus(false);
        $this->assertFalse($this->response->isValid());
    }

    /**
     * @covers \Laminas\ReCaptcha\Response::__construct
     * @covers \Laminas\ReCaptcha\Response::getErrorCodes
     * @covers \Laminas\ReCaptcha\Response::getStatus
     * @covers \Laminas\ReCaptcha\Response::setErrorCodes
     * @covers \Laminas\ReCaptcha\Response::setFromHttpResponse
     * @covers \Laminas\ReCaptcha\Response::setStatus
     * @throws Throwable
     */
    public function testSetFromHttpResponse(): void
    {
        $status       = false;
        $errorCodes   = ['foo', 'bar'];
        $responseBody = json_encode([
            'success'     => $status,
            'error-codes' => $errorCodes,
        ], JSON_THROW_ON_ERROR);
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $this->response->setFromHttpResponse($httpResponse);

        $this->assertFalse($this->response->getStatus());
        $this->assertSame($errorCodes, $this->response->getErrorCodes());
    }

    /**
     * @covers \Laminas\ReCaptcha\Response::__construct
     * @covers \Laminas\ReCaptcha\Response::getErrorCodes
     * @covers \Laminas\ReCaptcha\Response::getStatus
     * @covers \Laminas\ReCaptcha\Response::setErrorCodes
     * @covers \Laminas\ReCaptcha\Response::setStatus
     * @throws Throwable
     */
    public function testConstructor(): void
    {
        $status     = true;
        $errorCodes = ['ok'];

        $response = new ReCaptcha\Response($status, $errorCodes);

        $this->assertTrue($response->getStatus());
        $this->assertSame($errorCodes, $response->getErrorCodes());
    }

    /**
     * @covers \Laminas\ReCaptcha\Response::__construct
     * @covers \Laminas\ReCaptcha\Response::getErrorCodes
     * @covers \Laminas\ReCaptcha\Response::getStatus
     * @covers \Laminas\ReCaptcha\Response::setErrorCodes
     * @covers \Laminas\ReCaptcha\Response::setFromHttpResponse
     * @covers \Laminas\ReCaptcha\Response::setStatus
     * @throws Throwable
     */
    public function testConstructorWithHttpResponse(): void
    {
        $status       = false;
        $errorCodes   = ['foobar'];
        $responseBody = json_encode([
            'success'     => $status,
            'error-codes' => $errorCodes,
        ], JSON_THROW_ON_ERROR);
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $response = new ReCaptcha\Response(null, null, $httpResponse);

        $this->assertFalse($response->getStatus());
        $this->assertSame($errorCodes, $response->getErrorCodes());
    }
}
