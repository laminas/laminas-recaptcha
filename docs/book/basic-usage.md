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

When the form is submitted, you should receive one field: `g-recaptcha-response`.
Pass these to the reCAPTCHA object's `verify()` method:

```php
$result = $recaptcha->verify($_POST['g-recaptcha-response']);
```

Once you have the result, test against it to see if it is valid. The result is a
`Laminas\ReCaptcha\Response` object, which provides an `isValid()` method.

## Validating the reCAPTCHA

```php
if (! $result->isValid()) {
   // Failed validation
}
```

Another possibility is the use [`Laminas\Captcha`](https://docs.laminas.dev/laminas-captcha) adapter, or to use
that adapter as a backend for the [CAPTCHA form element](https://docs.laminas.dev/laminas-form/element/captcha/).
In each case, the details of rendering and validating the reCAPTCHA are automated.
