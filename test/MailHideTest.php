<?php
/**
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendServiceTest\ReCaptcha;

use PHPUnit\Framework\TestCase;
use ZendService\ReCaptcha;

class MailHideTest extends TestCase
{
    protected $mailHide   = null;

    public function setUp()
    {
        $this->publicKey  = getenv('TESTS_ZEND_SERVICE_RECAPTCHA_MAILHIDE_PUBLIC_KEY');
        $this->privateKey = getenv('TESTS_ZEND_SERVICE_RECAPTCHA_MAILHIDE_PRIVATE_KEY');

        if (! extension_loaded('mcrypt')) {
            $this->markTestSkipped('ZendService\ReCaptcha tests skipped due to missing mcrypt extension');
        }
        if (empty($this->publicKey)
            || $this->publicKey == 'public mailhide key'
            || empty($this->privateKey)
            || $this->privateKey == 'private mailhide key'
        ) {
            $this->markTestSkipped('ZendService\ReCaptcha\MailHide tests skipped due to missing keys');
        }
        $this->mailHide = new ReCaptcha\MailHide();
    }

    public function testSetGetPrivateKey()
    {
        $this->mailHide->setPrivateKey($this->privateKey);
        $this->assertSame($this->privateKey, $this->mailHide->getPrivateKey());
    }

    public function testSetGetEmail()
    {
        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->assertSame($mail, $this->mailHide->getEmail());
        $this->assertSame('example.com', $this->mailHide->getEmailDomainPart());
    }

    public function testEmailLocalPart()
    {
        $this->mailHide->setEmail('abcd@example.com');
        $this->assertSame('a', $this->mailHide->getEmailLocalPart());

        $this->mailHide->setEmail('abcdef@example.com');
        $this->assertSame('abc', $this->mailHide->getEmailLocalPart());

        $this->mailHide->setEmail('abcdefg@example.com');
        $this->assertSame('abcd', $this->mailHide->getEmailLocalPart());
    }

    public function testConstructor()
    {
        $mail = 'mail@example.com';

        $options = [
            'theme' => 'black',
            'lang' => 'no',
        ];

        $config = new \Zend\Config\Config($options);

        $mailHide = new ReCaptcha\MailHide($this->publicKey, $this->privateKey, $mail, $config);
        $_options = $mailHide->getOptions();

        $this->assertSame($this->publicKey, $mailHide->getPublicKey());
        $this->assertSame($this->privateKey, $mailHide->getPrivateKey());
        $this->assertSame($mail, $mailHide->getEmail());
        $this->assertSame($options['theme'], $_options['theme']);
        $this->assertSame($options['lang'], $_options['lang']);
    }

    protected function checkHtml($html)
    {
        $server = ReCaptcha\MailHide::MAILHIDE_SERVER;
        $pubKey = $this->publicKey;

        $this->assertEquals(2, substr_count($html, 'k=' . $pubKey));
        $this->assertRegexp('/c\=[a-zA-Z0-9_=-]+"/', $html);
        $this->assertRegexp('/c\=[a-zA-Z0-9_=-]+\\\'/', $html);
    }

    public function testGetHtml()
    {
        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->mailHide->setPublicKey($this->publicKey);
        $this->mailHide->setPrivateKey($this->privateKey);

        $html = $this->mailHide->getHtml();

        $this->checkHtml($html);
    }

    public function testGetHtmlWithNoEmail()
    {
        $this->expectException('ZendService\\ReCaptcha\\MailHideException');

        $html = $this->mailHide->getHtml();
    }

    public function testGetHtmlWithMissingPublicKey()
    {
        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->mailHide->setPrivateKey($this->privateKey);

        $this->expectException('ZendService\\ReCaptcha\\MailHideException');
        $html = $this->mailHide->getHtml();
    }

    public function testGetHtmlWithMissingPrivateKey()
    {
        $this->expectException('ZendService\\ReCaptcha\\MailHideException');

        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->mailHide->setPublicKey($this->publicKey);

        $html = $this->mailHide->getHtml();
    }

    public function testGetHtmlWithParamter()
    {
        $mail = 'mail@example.com';

        $this->mailHide->setPublicKey($this->publicKey);
        $this->mailHide->setPrivateKey($this->privateKey);

        $html = $this->mailHide->getHtml($mail);

        $this->checkHtml($html);
    }
}
