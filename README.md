# hCaptcha

Project based on [laravel-reCAPTCHA](https://github.com/Dylanchouxd/laravel-reCAPTCHA).

## Installation

```
composer require zbrettonye/hcaptcha
```

## Laravel 5 and above

> NOTE This package supports the auto-discovery feature of Laravel 5.5 and above, So skip these Setup instructions if
> you're using Laravel 5.5 and above.

### Setup

In `app/config/app.php` add the following :

1- The ServiceProvider to the providers array :

```php
ZBrettonYe\HCaptcha\HCaptchaServiceProvider::class,
```

2- The class alias to the aliases array :

```php
'HCaptcha' => ZBrettonYe\HCaptcha\Facades\HCaptcha::class,
```

3- Publish the config file

```ssh
php artisan vendor:publish --provider="ZBrettonYe\HCaptcha\HCaptchaServiceProvider"
```

### Configuration

You can get them from [here](https://docs.hcaptcha.com/api#getapikey)

#### Add the following to **.env** file

| key                | Required | Type   | note              | note                                                                                     |
|:-------------------|:--------:|:-------|:------------------|:-----------------------------------------------------------------------------------------|
| HCAPTCHA_SECRET    |    Y     | string |                   | Secret                                                                                   |
| HCAPTCHA_SITEKEY   |    Y     | string |                   | Site Key                                                                                 |
| HCAPTCHA_CONFIG    |    N     | bool   | false             | Method to get config                                                                     |
| HCAPTCHA_OPTIONS   |    N     | array  | ['timeout' => 30] | HTTP Options                                                                             |
| HCAPTCHA_SCORE     |    N     | bool   | false             | Enterprise feature, enable incorporate the score as a verification factor.               |
| HCAPTCHA_THRESHOLD |    N     | float  | 0.7               | once HCAPTCHA_SCORE is enabled. Any requests above this score will be considered as spam |

### Usage

#### Init js source

With default options :

```php
 {!! HCaptcha::renderJs() !!}
```

With [language support](https://docs.hcaptcha.com/configuration)
or [onloadCallback](https://docs.hcaptcha.com/configuration) option :

```php
 {!! HCaptcha::renderJs('fr', true, 'recaptchaCallback') !!}
```

#### Display reCAPTCHA

Default widget :

```php
{!! HCaptcha::display() !!}
```

With [custom attributes](https://docs.hcaptcha.com/configuration#themes) (theme, size, callback ...) :

```php
{!! HCaptcha::display(['data-theme' => 'dark']) !!}
```

Invisible reCAPTCHA using a [submit button](https://docs.hcaptcha.com/configuration#themes):

```php
{!! HCaptcha::displaySubmit('my-form-id', 'submit now!', ['data-theme' => 'dark']) !!}
```

Notice that the id of the form is required in this method to let the autogenerated
callback submit the form on a successful captcha verification.

#### Validation

Add `'h-captcha-response' => 'required|captcha'` to rules array :

```php
$validate = Validator::make(Input::all(), [
	'h-captcha-response' => 'required|captcha'
]);

```

##### Custom Validation Message

Add the following values to the `custom` array in the `validation` language file :

```php
'custom' => [
    'h-captcha-response' => [
        'required' => 'Please verify that you are not a robot.',
        'captcha' => 'Captcha error! try again later or contact site admin.',
    ],
],
```

Then check for captcha errors in the `Form` :

```php
@if ($errors->has('h-captcha-response'))
    <span class="help-block">
        <strong>{{ $errors->first('h-captcha-response') }}</strong>
    </span>
@endif
```

### Testing

When using the [Laravel Testing functionality](https://laravel.com/docs/10.x/testing), you will need to mock out the
response for the captcha form element.

So for any form tests involving the captcha, you can do this by mocking the facade behavior:

```php
// prevent validation error on captcha
HCaptcha::shouldReceive('verifyResponse')
    ->once()
    ->andReturn(true);

// provide hidden input for your 'required' validation
HCaptcha::shouldReceive('display')
    ->zeroOrMoreTimes()
    ->andReturn('<input type="hidden" name="h-captcha-response" value="1" />');
```

You can then test the remainder of your form as normal.

When using HTTP tests you can add the `h-captcha-response` to the request body for the 'required' validation:

```php
// prevent validation error on captcha
HCaptcha::shouldReceive('verifyResponse')
    ->once()
    ->andReturn(true);

// POST request, with request body including g-recaptcha-response
$response = $this->json('POST', '/register', [
    'h-captcha-response' => '1',
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => '123456',
    'password_confirmation' => '123456',
]);
```

## Without Laravel

Checkout example below:

```php
<?php

require_once "vendor/autoload.php";

$secret  = 'CAPTCHA-SECRET';
$sitekey = 'CAPTCHA-SITEKEY';
$captcha = new \ZBrettonYe\HCaptcha\HCaptcha($secret, $sitekey);

if (! empty($_POST)) {
    var_dump($captcha->verifyResponse($_POST['h-captcha-response']));
    exit();
}

?>

<form action="?" method="POST">
    <?php echo $captcha->display(); ?>
    <button type="submit">Submit</button>
</form>

<?php echo $captcha->renderJs(); ?>
```
