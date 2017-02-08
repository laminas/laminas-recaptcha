<?php
/**
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendServiceTest\ReCaptcha;

use PHPUnit\Framework\TestCase;
use ZendService\ReCaptcha;
use Zend\Http\Response;

class ResponseTest extends TestCase
{
    protected $response = null;

    public function setUp()
    {
        $this->response = new ReCaptcha\Response();
    }

    public function testSetAndGet()
    {
        /* Set and get status */
        $status = true;
        $this->response->setStatus($status);
        $this->assertSame(true, $this->response->getStatus());

        $status = false;
        $this->response->setStatus($status);
        $this->assertSame(false, $this->response->getStatus());

        /* Set and get the error codes */
        $errorCodes = 'foobar';
        $this->response->setErrorCodes($errorCodes);
        $this->assertSame([$errorCodes], $this->response->getErrorCodes());

        $errorCodes = ['foo', 'bar'];
        $this->response->setErrorCodes($errorCodes);
        $this->assertSame($errorCodes, $this->response->getErrorCodes());
    }

    public function testIsValid()
    {
        $this->response->setStatus(true);
        $this->assertSame(true, $this->response->isValid());
    }

    public function testIsInvalid()
    {
        $this->response->setStatus(false);
        $this->assertSame(false, $this->response->isValid());
    }

    public function testSetFromHttpResponse()
    {
        $status       = false;
        $errorCodes    = ['foo', 'bar'];
        $responseBody = json_encode([
            'success' => $status,
            'error-codes' => $errorCodes
        ]);
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $this->response->setFromHttpResponse($httpResponse);

        $this->assertSame(false, $this->response->getStatus());
        $this->assertSame($errorCodes, $this->response->getErrorCodes());
    }

    public function testConstructor()
    {
        $status = true;
        $errorCodes = ['ok'];

        $response = new ReCaptcha\Response($status, $errorCodes);

        $this->assertSame(true, $response->getStatus());
        $this->assertSame($errorCodes, $response->getErrorCodes());
    }

    public function testConstructorWithHttpResponse()
    {
        $status       = false;
        $errorCodes   = ['foobar'];
        $responseBody = json_encode([
            'success' => $status,
            'error-codes' => $errorCodes
        ]);
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $response = new ReCaptcha\Response(null, null, $httpResponse);

        $this->assertSame(false, $response->getStatus());
        $this->assertSame($errorCodes, $response->getErrorCodes());
    }
}
