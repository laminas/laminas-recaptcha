<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha;

use Exception as PhpException;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Laminas\ReCaptcha\Contract\ReCaptchaInterface;
use Laminas\ReCaptcha\Contract\ResponseInterface;
use Laminas\ReCaptcha\Response;
use Laminas\Stdlib\ArrayUtils;
use Throwable;
use Traversable;

use function array_key_exists;
use function sprintf;
use function trigger_error;

use const E_USER_WARNING;

/**
 * Render and verify ReCaptcha
 */
final class ReCaptcha implements ReCaptchaInterface
{
    /**
     * URI to the API
     *
     * @var string
     */
    public const API_SERVER = 'https://www.google.com/recaptcha/api';

    /**
     * URI to the site verify endpoint
     *
     * @var string
     */
    public const VERIFY_SERVER = 'https://www.google.com/recaptcha/api/siteverify';

    /** Site key used when displaying the captcha */
    private ?string $siteKey = null;

    /** Secret key used when verifying user input */
    private ?string $secretKey = null;

    /** Ip address used when verifying user input */
    private ?string $ip = null;

    /** Parameters for the object
     *
     * @var mixed[] */
    private array $params = [
        'noscript' => false, /* Includes the <noscript> tag */
    ];

    /**
     * Options for tailoring reCaptcha
     *
     * See the different options on https://developers.google.com/recaptcha/docs/display#config
     *
     * @var array<string,mixed>
     */
    private array $options = [
        'theme'            => 'light',
        'type'             => 'image',
        'size'             => 'normal',
        'tabindex'         => 0,
        'callback'         => null,
        'expired-callback' => null,
        'hl'               => null, // Auto-detect language
    ];

    private ?HttpClient $httpClient = null;

    /** @throws Exception */
    public function __construct(
        ?string $siteKey = null,
        ?string $secretKey = null,
        ?iterable $params = [],
        ?iterable $options = [],
        ?string $ip = null,
        ?HttpClient $httpClient = null
    ) {
        if ($siteKey !== null) {
            $this->setSiteKey($siteKey);
        }

        if ($secretKey !== null) {
            $this->setSecretKey($secretKey);
        }

        $this->setIp(
            $ip ??
                // https://developers.cloudflare.com/fundamentals/get-started/reference/http-request-headers/#cf-connecting-ip
                $_SERVER['HTTP_CF_CONNECTING_IP'] ??
                $_SERVER['HTTP_CLIENT_IP'] ??
                $_SERVER['HTTP_X_FORWARDED_FOR'] ??
                $_SERVER['HTTP_X_FORWARDED'] ??
                $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] ??
                $_SERVER['HTTP_FORWARDED_FOR'] ??
                $_SERVER['HTTP_FORWARDED'] ??
                $_SERVER['REMOTE_ADDR']
        );

        if ($params !== null) {
            $this->setParams($params);
        }

        if ($options !== null) {
            $this->setOptions($options);
        }

        $this->setHttpClient($httpClient ?: new HttpClient());
    }

    public function setHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Serialize as string
     *
     * When the instance is used as a string it will display the recaptcha.
     * Since we can't throw exceptions within this method we will trigger
     * a user warning instead.
     */
    public function __toString(): string
    {
        try {
            $return = $this->getHtml();
        } catch (PhpException $phpException) {
            $return = '';
            trigger_error($phpException->getMessage(), E_USER_WARNING);
        }

        return $return;
    }

    /**
     * Set the ip property
     */
    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /** Get the ip property */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Set a single parameter
     *
     * @param mixed $value
     */
    public function setParam(string $key, $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Set parameters
     *
     * @throws Exception
     */
    public function setParams(iterable $params): self
    {
        if ($params instanceof Traversable) {
            $params = ArrayUtils::iteratorToArray($params);
        }

        foreach ($params as $k => $v) {
            $this->setParam($k, $v);
        }

        return $this;
    }

    /**
     * Get the parameter array
     *
     * @return array<string,mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get a single parameter
     *
     * @return mixed
     */
    public function getParam(string $key)
    {
        return $this->params[$key] ?? null;
    }

    /**
     * Set a single option
     *
     * @param mixed $value
     */
    public function setOption(string $key, $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set options
     *
     * @param iterable<string,mixed> $options
     * @throws Exception
     */
    public function setOptions(iterable $options): self
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        foreach ($options as $k => $v) {
            $this->setOption($k, $v);
        }

        return $this;
    }

    /**
     * Get the options array
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get a single option
     *
     * @return mixed
     */
    public function getOption(string $key)
    {
        if (! isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }

    /**
     * Get the site key
     */
    public function getSiteKey(): ?string
    {
        return $this->siteKey;
    }

    /**
     * Set the site key
     */
    public function setSiteKey(string $siteKey): self
    {
        $this->siteKey = $siteKey;

        return $this;
    }

    /**
     * Get the secret key
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * Set the secret key
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Get the HTML code for the captcha
     *
     * This method uses the public key to fetch a recaptcha form.
     *
     * @throws Exception
     */
    public function getHtml(): string
    {
        if ($this->siteKey === null) {
            throw new Exception('Missing site key');
        }

        // Should we use an onload callback?
        if (array_key_exists('onload', $this->options)) {
            return sprintf(
                '<script type="text/javascript" src="%s.js?onload=%s&render=explicit" async defer></script>',
                self::API_SERVER,
                $this->options['onload']
            );
        }

        $langOption = '';

        if (! empty($this->options['hl'])) {
            $langOption = sprintf('?hl=%s', $this->options['hl']);
        }

        $data = sprintf('data-sitekey="%s"', $this->siteKey);

        foreach (['theme', 'type', 'size', 'tabindex', 'callback', 'expired-callback'] as $option) {
            if (! empty($this->options[$option])) {
                $data .= sprintf(' data-%s="%s"', $option, $this->options[$option]);
            }
        }

        $return = <<<HTML
<script type="text/javascript" src="{self::API_SERVER}.js{$langOption}" async defer></script>
<div class="g-recaptcha" {$data}></div>
HTML;

        if ($this->params['noscript']) {
            $return .= <<<HTML
<noscript>
  <div style="width: 302px; height: 422px;">
    <div style="width: 302px; height: 422px; position: relative;">
      <div style="width: 302px; height: 422px; position: absolute;">
        <iframe src="{self::API_SERVER}/fallback?k={$this->siteKey}"
                border="0" scrolling="no"
                style="width: 302px; height:422px; border-style: none;">
        </iframe>
      </div>
      <div style="width: 300px; height: 60px; border-style: none;
                  bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
                  background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
        <textarea id="g-recaptcha-response" name="g-recaptcha-response"
                  class="g-recaptcha-response"
                  style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
                         margin: 10px 25px; padding: 0px; resize: none;" >
        </textarea>
      </div>
    </div>
  </div>
</noscript>
HTML;
        }

        return $return;
    }

    /**
     * Posts a solution to the site verify endpoint
     *
     * @throws Exception
     */
    private function post(string $responseField): HttpResponse
    {
        if ($this->secretKey === null) {
            throw new Exception('Missing secret key');
        }

        if ($this->ip === null) {
            throw new Exception('Missing ip address');
        }

        $request = new HttpRequest();
        $request->setUri(self::VERIFY_SERVER);
        $request->setMethod(HttpRequest::METHOD_POST);
        $request->getPost()->fromArray([
            'secret'   => $this->secretKey,
            'remoteip' => $this->ip,
            'response' => $responseField,
        ]);

        $this->httpClient->setEncType(HttpClient::ENC_URLENCODED);

        return $this->httpClient->send($request);
    }

    /**
     * Verify the user input
     *
     * @throws Throwable
     */
    public function verify(string $responseField): ResponseInterface
    {
        return new Response(null, null, $this->post($responseField));
    }
}
