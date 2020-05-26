# Basic Usage

## Creating an instance of the reCAPTCHA service

Instantiate a `Laminas\ReCaptcha\ReCaptcha` object, passing it your public and private keys:

```php
$recaptcha = new Laminas\ReCaptcha\ReCaptcha($pubKey, $privKey);
```

## Displaying the reCAPTCHA

To render the reCAPTCHA, call the `getHTML()` method:

```php
echo $recaptcha->getHTML();
```

## Verifying the form fields

When the form is submitted, you should receive the field `g-recaptcha-response`
in your submission.  Pass that value to the reCAPTCHA object's `verify()`
method:

```php
// Assuming a POST request was made:
$result = $recaptcha->verify($_POST['g-recaptcha-response']);
```

Once you have the result, test against it to see if it is valid. The result is a
`Laminas\ReCaptcha\Response` object, which provides an `isValid()` method:

```php
// Validating the reCAPTCHA:
if (! $result->isValid()) {
   // Failed validation
}
```

If you wish to automate the details of rendering and validating the reCAPTCHA,
you should investigate the [laminas-captcha reCAPTCHA adapter](https://docs.laminas.dev/laminas-captcha/adapters/#laminascaptcharecaptcha),
or use that adapter as a backend for the [CAPTCHA form element](https://docs.laminas.dev/laminas-form/element/captcha/).
