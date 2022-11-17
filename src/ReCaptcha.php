<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha;

use Exception as PhpException;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Request as HttpRequest;
use Laminas\ReCaptcha\Response;
use Laminas\Stdlib\ArrayUtils;
use Stringable;
use Traversable;

use function get_debug_type;
use function is_array;
use function sprintf;
use function trigger_error;

use const E_USER_WARNING;

/**
 * Render and verify ReCaptchas
 */
class ReCaptcha implements Stringable
{
    /**
     * URI to the API
     *
     * @var string
     */
    public const API_SERVER = 'https://www.google.com/recaptcha/api';

    /**
     * URI to the verify server
     *
     * @var string
     */
    public const VERIFY_SERVER = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Parameters for the object
     */
    private array $params = [
        'noscript' => false, /* Includes the <noscript> tag */
    ];

    /**
     * Options for tailoring reCaptcha
     *
     * See the different options on https://developers.google.com/recaptcha/docs/display#config
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

    private HttpClient $httpClient;

    public function __construct(
        /** Site key used when displaying the captcha */
        private ?string $siteKey = null,
        /** Secret key used when verifying user input */
        private ?string $secretKey = null,
        array|Traversable|null $params = null,
        array|Traversable|null $options = null,
        /** Ip address used when verifying user input */
        private ?string $ip = null,
        ?HttpClient $httpClient = null
    ) {
        if ($siteKey !== null) {
            $this->setSiteKey($siteKey);
        }

        if ($secretKey !== null) {
            $this->setSecretKey($secretKey);
        }

        if ($ip !== null) {
            $this->setIp($ip);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $this->setIp($_SERVER['REMOTE_ADDR']);
        }

        if ($params !== null) {
            $this->setParams($params);
        }

        if ($options !== null) {
            $this->setOptions($options);
        }

        $this->setHttpClient($httpClient ?: new HttpClient());
    }

    /** @return $this */
    public function setHttpClient(HttpClient $httpClient): static
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
    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get the ip property
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * Set a single parameter
     */
    public function setParam(string $key, string $value): static
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Set parameters
     *
     * @param array|Traversable $params
     * @throws Exception
     */
    public function setParams(iterable $params): static
    {
        if ($params instanceof Traversable) {
            $params = ArrayUtils::iteratorToArray($params);
        }

        if (! is_array($params)) {
            throw new Exception(sprintf(
                '%s expects an array or Traversable set of params; received "%s"',
                __METHOD__,
                get_debug_type($params)
            ));
        }

        foreach ($params as $k => $v) {
            $this->setParam($k, $v);
        }

        return $this;
    }

    /**
     * Get the parameter array
     *
     * @return mixed[]
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
        if (! isset($this->params[$key])) {
            return null;
        }

        return $this->params[$key];
    }

    /**
     * Set a single option
     */
    public function setOption(string $key, string $value): static
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set options
     *
     * @throws Exception
     */
    public function setOptions(array|Traversable $options): static
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (is_array($options)) {
            foreach ($options as $k => $v) {
                $this->setOption($k, $v);
            }
        } else {
            throw new Exception('Expected array or Traversable object');
        }

        return $this;
    }

    /**
     * Get the options array
     *
     * @return mixed[]
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
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Set the site key
     */
    public function setSiteKey(string $siteKey): static
    {
        $this->siteKey = $siteKey;

        return $this;
    }

    /**
     * Get the secret key
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * Set the secret key
     */
    public function setSecretKey(string $secretKey): static
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

        $host = self::API_SERVER;

        // Should we use an onload callback?
        if (! empty($this->options['onload'])) {
            return sprintf(
                '<script type="text/javascript" src="%s.js?onload=%s&render=explicit" async defer></script>',
                $host,
                $this->options['onload']
            );
        }

        $langOption = '';

        if (! empty($this->options['hl'])) {
            $langOption = sprintf('?hl=%s', $this->options['hl']);
        }

        $data = sprintf('data-sitekey="%s"', $this->siteKey);

        foreach (
            [
                'theme',
                'type',
                'size',
                'tabindex',
                'callback',
                'expired-callback',
            ] as $option
        ) {
            if (! empty($this->options[$option])) {
                $data .= sprintf(' data-%s="%s"', $option, $this->options[$option]);
            }
        }

        $return = <<<HTML
<script type="text/javascript" src="{$host}.js{$langOption}" async defer></script>
<div class="g-recaptcha" {$data}></div>
HTML;

        if ($this->params['noscript']) {
            $return .= <<<HTML
<noscript>
  <div style="width: 302px; height: 422px;">
    <div style="width: 302px; height: 422px; position: relative;">
      <div style="width: 302px; height: 422px; position: absolute;">
        <iframe src="{$host}/fallback?k={$this->siteKey}"
                frameborder="0" scrolling="no"
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
     * Gets a solution to the verify server
     *
     * @throws Exception
     */
    private function post(string $responseField): \Laminas\Http\Response
    {
        if ($this->secretKey === null) {
            throw new Exception('Missing secret key');
        }

        if ($this->ip === null) {
            throw new Exception('Missing ip address');
        }

        /* Fetch an instance of the http client */
        $httpClient = $this->getHttpClient();

        $params = [
            'secret'   => $this->secretKey,
            'remoteip' => $this->ip,
            'response' => $responseField,
        ];

        $request = new HttpRequest();
        $request->setUri(self::VERIFY_SERVER);
        $request->getPost()->fromArray($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $httpClient->setEncType($httpClient::ENC_URLENCODED);

        return $httpClient->send($request);
    }

    /**
     * Verify the user input
     *
     * This method calls up the post method and returns a
     * \Laminas\ReCaptcha\Response object.
     */
    public function verify(string $responseField): Response
    {
        $response = $this->post($responseField);
        return new Response(null, [], $response);
    }
}
