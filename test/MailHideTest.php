<?php

declare(strict_types=1);

namespace LaminasTest\ReCaptcha;

use Laminas\Config\Config;
use Laminas\ReCaptcha;
use Laminas\ReCaptcha\MailHide;
use Laminas\ReCaptcha\MailHideException;
use Override;
use PHPUnit\Framework\TestCase;

use function extension_loaded;
use function getenv;
use function substr_count;

final class MailHideTest extends TestCase
{
    private string $publicKey;

    private string $privateKey;

    private MailHide $mailHide;

    #[Override]
    protected function setUp(): void
    {
        $this->publicKey  = getenv('TESTS_LAMINAS_SERVICE_RECAPTCHA_MAILHIDE_PUBLIC_KEY');
        $this->privateKey = getenv('TESTS_LAMINAS_SERVICE_RECAPTCHA_MAILHIDE_PRIVATE_KEY');

        if (! extension_loaded('mcrypt')) {
            $this->markTestSkipped('Laminas\ReCaptcha tests skipped due to missing mcrypt extension');
        }
        if (
            empty($this->publicKey)
            || $this->publicKey === 'public mailhide key'
            || empty($this->privateKey)
            || $this->privateKey === 'private mailhide key'
        ) {
            $this->markTestSkipped('Laminas\ReCaptcha\MailHide tests skipped due to missing keys');
        }
        $this->mailHide = new ReCaptcha\MailHide();
    }

    public function testSetGetPrivateKey(): void
    {
        $this->mailHide->setPrivateKey($this->privateKey);
        $this->assertSame($this->privateKey, $this->mailHide->getPrivateKey());
    }

    public function testSetGetEmail(): void
    {
        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->assertSame($mail, $this->mailHide->getEmail());
        $this->assertSame('example.com', $this->mailHide->getEmailDomainPart());
    }

    public function testEmailLocalPart(): void
    {
        $this->mailHide->setEmail('abcd@example.com');
        $this->assertSame('a', $this->mailHide->getEmailLocalPart());

        $this->mailHide->setEmail('abcdef@example.com');
        $this->assertSame('abc', $this->mailHide->getEmailLocalPart());

        $this->mailHide->setEmail('abcdefg@example.com');
        $this->assertSame('abcd', $this->mailHide->getEmailLocalPart());
    }

    public function testConstructor(): void
    {
        $mail = 'mail@example.com';

        $options = [
            'theme' => 'black',
            'lang'  => 'no',
        ];

        $config = new Config($options);

        $mailHide        = new ReCaptcha\MailHide($this->publicKey, $this->privateKey, $mail, $config);
        $receivedOptions = $mailHide->getOptions();

        $this->assertSame($this->publicKey, $mailHide->getPublicKey());
        $this->assertSame($this->privateKey, $mailHide->getPrivateKey());
        $this->assertSame($mail, $mailHide->getEmail());
        $this->assertSame($options['theme'], $receivedOptions['theme']);
        $this->assertSame($options['lang'], $receivedOptions['lang']);
    }

    protected function checkHtml(string $html): void
    {
        $pubKey = $this->publicKey;

        $this->assertEquals(2, substr_count($html, 'k=' . $pubKey));
        $this->assertRegExp('/c\=[a-zA-Z0-9_=-]+"/', $html);
        $this->assertRegExp('/c\=[a-zA-Z0-9_=-]+\\\'/', $html);
    }

    public function testGetHtml(): void
    {
        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->mailHide->setPublicKey($this->publicKey);
        $this->mailHide->setPrivateKey($this->privateKey);

        $html = $this->mailHide->getHtml();

        $this->checkHtml($html);
    }

    public function testGetHtmlWithNoEmail(): void
    {
        $this->expectException(MailHideException::class);

        $this->mailHide->getHtml();
    }

    public function testGetHtmlWithMissingPublicKey(): void
    {
        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->mailHide->setPrivateKey($this->privateKey);

        $this->expectException(MailHideException::class);
        $this->mailHide->getHtml();
    }

    public function testGetHtmlWithMissingPrivateKey(): void
    {
        $this->expectException(MailHideException::class);

        $mail = 'mail@example.com';

        $this->mailHide->setEmail($mail);
        $this->mailHide->setPublicKey($this->publicKey);

        $this->mailHide->getHtml();
    }

    public function testGetHtmlWithParamter(): void
    {
        $mail = 'mail@example.com';

        $this->mailHide->setPublicKey($this->publicKey);
        $this->mailHide->setPrivateKey($this->privateKey);

        $html = $this->mailHide->getHtml($mail);

        $this->checkHtml($html);
    }
}
