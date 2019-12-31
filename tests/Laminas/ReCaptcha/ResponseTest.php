<?php

/**
 * @see       https://github.com/laminas/laminas-recaptcha for the canonical source repository
 * @copyright https://github.com/laminas/laminas-recaptcha/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-recaptcha/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ReCaptcha;

use Laminas\Http\Response;
use Laminas\ReCaptcha;

/**
 * @category   Laminas
 * @package    Laminas_Service_ReCaptcha
 * @subpackage UnitTests
 * @group      Laminas_Service
 * @group      Laminas_Service_ReCaptcha
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $_response = null;

    public function setUp()
    {
        $this->_response = new ReCaptcha\Response();
    }

    public function testSetAndGet()
    {
        /* Set and get status */
        $status = 'true';
        $this->_response->setStatus($status);
        $this->assertSame(true, $this->_response->getStatus());

        $status = 'false';
        $this->_response->setStatus($status);
        $this->assertSame(false, $this->_response->getStatus());

        /* Set and get the error code */
        $errorCode = 'foobar';
        $this->_response->setErrorCode($errorCode);
        $this->assertSame($errorCode, $this->_response->getErrorCode());
    }

    public function testIsValid()
    {
        $this->_response->setStatus('true');
        $this->assertSame(true, $this->_response->isValid());
    }

    public function testIsInvalid()
    {
        $this->_response->setStatus('false');
        $this->assertSame(false, $this->_response->isValid());
    }

    public function testSetFromHttpResponse()
    {
        $status       = 'false';
        $errorCode    = 'foobar';
        $responseBody = $status . "\n" . $errorCode;
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $this->_response->setFromHttpResponse($httpResponse);

        $this->assertSame(false, $this->_response->getStatus());
        $this->assertSame($errorCode, $this->_response->getErrorCode());
    }

    public function testConstructor()
    {
        $status = 'true';
        $errorCode = 'ok';

        $response = new ReCaptcha\Response($status, $errorCode);

        $this->assertSame(true, $response->getStatus());
        $this->assertSame($errorCode, $response->getErrorCode());
    }

    public function testConstructorWithHttpResponse()
    {
        $status       = 'false';
        $errorCode    = 'foobar';
        $responseBody = $status . "\n" . $errorCode;
        $httpResponse = new Response();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html');
        $httpResponse->setContent($responseBody);

        $response = new ReCaptcha\Response(null, null, $httpResponse);

        $this->assertSame(false, $response->getStatus());
        $this->assertSame($errorCode, $response->getErrorCode());
    }
}
