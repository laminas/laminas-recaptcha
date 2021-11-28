# Introduction

`Laminas\ReCaptcha\ReCaptcha` provides a client for the [reCAPTCHA Web Service](https://www.google.com/recaptcha/).
Per the reCAPTCHA site, "reCAPTCHA is a free CAPTCHA service that helps to digitize books". Each reCAPTCHA requires
the user to input two words, the first of which is the actual CAPTCHA, and the second of which is a word from some
scanned text that Optical Character Recognition (OCR) software has been unable to identify. The assumption is that
if a user correctly provides the first word, the second is likely correctly entered as well, and can be used to
improve OCR software for digitizing books.

<!-- markdownlint-disable-next-line header-increment -->
> ### Additional Requirements
>
> In order to use the reCAPTCHA service, you will need to
> [sign up for an account](https://www.google.com/recaptcha/admin) and register
> one or more domains with the service in order to generate public and private
> keys.
