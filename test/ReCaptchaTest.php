<?php

/**
 * @see       https://github.com/laminas/laminas-recaptcha for the canonical source repository
 * @copyright https://github.com/laminas/laminas-recaptcha/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-recaptcha/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ReCaptcha;

use Laminas\Config;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\Http\Client\Adapter\Test;
use Laminas\ReCaptcha\Exception;
use Laminas\ReCaptcha\ReCaptcha;
use Laminas\ReCaptcha\Response as ReCaptchaResponse;
use PHPUnit\Framework\TestCase;

use function getenv;
use function sprintf;
use function strstr;

class ReCaptchaTest extends TestCase
{
    /** @var string */
    private $siteKey;

    /** @var string */
    private $secretKey;

    /** @var ReCaptcha */
    private $reCaptcha;

    protected function setUp(): void
    {
        $this->siteKey   = getenv('TESTS_LAMINAS_SERVICE_RECAPTCHA_SITE_KEY');
        $this->secretKey = getenv('TESTS_LAMINAS_SERVICE_RECAPTCHA_SECRET_KEY');

        if (empty($this->siteKey) || empty($this->siteKey)) {
            $this->markTestSkipped('Laminas\ReCaptcha\ReCaptcha tests skipped due to missing keys');
        }

        $httpClient = new HttpClient(
            null,
            [
                'adapter' => Curl::class,
            ]
        );

        $this->reCaptcha = new ReCaptcha(null, null, null, null, null, $httpClient);
    }

    public function testSetAndGet()
    {
        /* Set and get IP address */
        $ip = '127.0.0.1';
        $this->reCaptcha->setIp($ip);
        $this->assertSame($ip, $this->reCaptcha->getIp());

        /* Set and get site key */
        $this->reCaptcha->setSiteKey($this->siteKey);
        $this->assertSame($this->siteKey, $this->reCaptcha->getSiteKey());

        /* Set and get secret key */
        $this->reCaptcha->setSecretKey($this->secretKey);
        $this->assertSame($this->secretKey, $this->reCaptcha->getSecretKey());
    }

    public function testSingleParam()
    {
        $key   = 'ssl';
        $value = true;

        $this->reCaptcha->setParam($key, $value);
        $this->assertSame($value, $this->reCaptcha->getParam($key));
    }

    public function testGetNonExistingParam()
    {
        $this->assertNull($this->reCaptcha->getParam('foobar'));
    }

    public function testMultipleParams()
    {
        $params = [
            'ssl' => true,
        ];

        $this->reCaptcha->setParams($params);
        $receivedParams = $this->reCaptcha->getParams();

        $this->assertSame($params['ssl'], $receivedParams['ssl']);
    }

    public function testSingleOption()
    {
        $key   = 'theme';
        $value = 'dark';

        $this->reCaptcha->setOption($key, $value);
        $this->assertSame($value, $this->reCaptcha->getOption($key));
    }

    public function testGetNonExistingOption()
    {
        $this->assertNull($this->reCaptcha->getOption('foobar'));
    }

    public function testMultipleOptions()
    {
        $options = [
            'theme' => 'dark',
            'hl'    => 'en',
        ];

        $this->reCaptcha->setOptions($options);
        $receivedOptions = $this->reCaptcha->getOptions();

        $this->assertSame($options['theme'], $receivedOptions['theme']);
        $this->assertSame($options['hl'], $receivedOptions['hl']);
    }

    public function testSetMultipleParamsFromLaminasConfig()
    {
        $params = [
            'ssl' => true,
        ];

        $config = new Config\Config($params);

        $this->reCaptcha->setParams($config);
        $receivedParams = $this->reCaptcha->getParams();

        $this->assertSame($params['ssl'], $receivedParams['ssl']);
    }

    public function testSetInvalidParams()
    {
        $this->expectException(Exception::class);
        $var = 'string';
        $this->reCaptcha->setParams($var);
    }

    public function testSetMultipleOptionsFromLaminasConfig()
    {
        $options = [
            'theme' => 'dark',
            'hl'    => 'en',
        ];

        $config = new Config\Config($options);

        $this->reCaptcha->setOptions($config);
        $receivedOptions = $this->reCaptcha->getOptions();

        $this->assertSame($options['theme'], $receivedOptions['theme']);
        $this->assertSame($options['hl'], $receivedOptions['hl']);
    }

    public function testSetInvalidOptions()
    {
        $this->expectException(Exception::class);
        $var = 'string';
        $this->reCaptcha->setOptions($var);
    }

    public function testConstructor()
    {
        $params = [
            'noscript' => true,
        ];

        $options = [
            'theme' => 'dark',
            'hl'    => 'en',
        ];

        $ip = '127.0.0.1';

        $reCaptcha = new ReCaptcha($this->siteKey, $this->secretKey, $params, $options, $ip);

        $receivedParams  = $reCaptcha->getParams();
        $receivedOptions = $reCaptcha->getOptions();

        $this->assertSame($this->siteKey, $reCaptcha->getSiteKey());
        $this->assertSame($this->secretKey, $reCaptcha->getSecretKey());
        $this->assertSame($params['noscript'], $receivedParams['noscript']);
        $this->assertSame($options['theme'], $receivedOptions['theme']);
        $this->assertSame($options['hl'], $receivedOptions['hl']);
        $this->assertSame($ip, $reCaptcha->getIp());
    }

    public function testConstructorWithNoIp()
    {
        // Fake the _SERVER value
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $reCaptcha = new ReCaptcha(null, null, null, null, null);

        $this->assertSame($_SERVER['REMOTE_ADDR'], $reCaptcha->getIp());

        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testGetHtmlWithNoPublicKey()
    {
        $this->expectException(Exception::class);

        $this->reCaptcha->getHtml();
    }

    public function testVerify()
    {
        $this->reCaptcha->setSiteKey($this->siteKey);
        $this->reCaptcha->setSecretKey($this->secretKey);
        $this->reCaptcha->setIp('127.0.0.1');

        $adapter = new Test();
        $client  = new HttpClient(null, [
            'adapter' => $adapter,
        ]);

        $this->reCaptcha->setHttpClient($client);

        $resp = $this->reCaptcha->verify('responseField');

        // See if we have a valid object and that the status is false
        $this->assertInstanceOf(ReCaptchaResponse::class, $resp);
        $this->assertFalse($resp->getStatus());
    }

    public function testGetHtml()
    {
        $this->reCaptcha->setSiteKey($this->siteKey);

        $html = $this->reCaptcha->getHtml();

        // See if the options for the captcha exist in the string
        $this->assertNotFalse(strstr($html, sprintf('data-sitekey="%s"', $this->siteKey)));

        // See if the js/iframe src is correct
        $this->assertNotTrue(strstr($html, '<iframe'));
    }

    public function testGetHtmlWithLanguage()
    {
        $this->reCaptcha->setSiteKey($this->siteKey);
        $this->reCaptcha->setOption('hl', 'en');

        $html = $this->reCaptcha->getHtml();

        $this->assertStringContainsString('?hl=en', $html);
    }

    /** @group Laminas-10991 */
    public function testHtmlGenerationWithNoScriptElements()
    {
        $this->reCaptcha->setSiteKey($this->siteKey);
        $this->reCaptcha->setParam('noscript', true);
        $html = $this->reCaptcha->getHtml();
        $this->assertStringContainsString('<iframe', $html);
    }

    public function testVerifyWithMissingSecretKey()
    {
        $this->expectException(Exception::class);

        $this->reCaptcha->verify('response');
    }

    public function testVerifyWithMissingIp()
    {
        $this->expectException(Exception::class);

        $this->reCaptcha->setSecretKey($this->secretKey);
        $this->reCaptcha->verify('response');
    }
}
