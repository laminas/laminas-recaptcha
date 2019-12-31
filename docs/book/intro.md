# laminas-recaptcha

`Laminas\ReCaptcha\ReCaptcha` provides a client for the [reCAPTCHA Web Service](https://www.google.com/recaptcha/).
Per the reCAPTCHA site, "reCAPTCHA is a free CAPTCHA service that helps to digitize books". Each reCAPTCHA requires
the user to input two words, the first of which is the actual CAPTCHA, and the second of which is a word from some
scanned text that Optical Character Recognition (OCR) software has been unable to identify. The assumption is that
if a user correctly provides the first word, the second is likely correctly entered as well, and can be used to
improve OCR software for digitizing books.

In order to use the reCAPTCHA service, you will need to [sign up for an account](https://www.google.com/recaptcha/admin)
and register one or more domains with the service in order to generate public and private keys.

## Simplest use

Instantiate a `Laminas\ReCaptcha\ReCaptcha` object, passing it your public and private keys:


### Creating an instance of the reCAPTCHA service

```php
$recaptcha = new Laminas\ReCaptcha\ReCaptcha($pubKey, $privKey);
```

### Displaying the reCAPTCHA

To render the reCAPTCHA, simply call the `getHTML()` method:

```php
echo $recaptcha->getHTML();
```

### Verifying the form fields

When the form is submitted, you should receive one field: `g-recaptcha-response`.
Pass these to the reCAPTCHA object's `verify()` method:

```php
$result = $recaptcha->verify($_POST['g-recaptcha-response']);
```

Once you have the result, test against it to see if it is valid. The result is a
`Laminas\ReCaptcha\Response` object, which provides an `isValid()` method.

### Validating the reCAPTCHA

```php
if (!$result->isValid()) {
   // Failed validation
}
```

It is even simpler to use [`Laminas\Captcha`](https://docs.laminas.dev/laminas-captcha) adapter, or to use
that adapter as a backend for the [CAPTCHA form element](https://docs.laminas.dev/laminas-form/element/captcha/).
In each case, the details of rendering and validating the reCAPTCHA are automated for you.
