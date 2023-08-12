<?php

use PHPUnit\Framework\TestCase;
use ZBrettonYe\HCaptcha\HCaptcha;

class HCaptchaTest extends TestCase
{
    /**
     * @var HCaptchaTest
     */
    private $captcha;

    public function setUp(): void
    {
        parent::setUp();
        $this->captcha = new hCaptcha('{secret-key}', '{site-key}');
    }

    public function testJsLink()
    {
        self::assertInstanceOf(HCaptcha::class, $this->captcha);

        $simple = '<script src="https://hcaptcha.com/1/api.js?" async defer></script>'."\n";
        $withLang = '<script src="https://hcaptcha.com/1/api.js?hl=vi" async defer></script>'."\n";
        $withCallback = '<script src="https://hcaptcha.com/1/api.js?render=explicit&onload=myOnloadCallback" async defer></script>'."\n";

        self::assertEquals($simple, $this->captcha->renderJs());
        self::assertEquals($withLang, $this->captcha->renderJs('vi'));
        self::assertEquals($withCallback, $this->captcha->renderJs(null, true, 'myOnloadCallback'));
    }

    public function testDisplay()
    {
        self::assertInstanceOf(HCaptcha::class, $this->captcha);

        $simple = '<div data-sitekey="{site-key}" class="h-captcha"></div>';
        $withAttrs = '<div data-theme="light" data-sitekey="{site-key}" class="h-captcha"></div>';

        self::assertEquals($simple, $this->captcha->display());
        self::assertEquals($withAttrs, $this->captcha->display(['data-theme' => 'light']));
    }

    public function testdisplaySubmit()
    {
        self::assertInstanceOf(HCaptcha::class, $this->captcha);

        $javascript = '<script>function onSubmittest(){document.getElementById("test").submit();}</script>';
        $simple = '<button data-callback="onSubmittest" data-sitekey="{site-key}" class="h-captcha"><span>submit</span></button>';
        $withAttrs = '<button data-theme="light" class="h-captcha 123" data-callback="onSubmittest" data-sitekey="{site-key}"><span>submit123</span></button>';

        self::assertEquals($simple.$javascript, $this->captcha->displaySubmit('test'));
        $withAttrsResult = $this->captcha->displaySubmit('test', 'submit123', ['data-theme' => 'light', 'class' => '123']);
        self::assertEquals($withAttrs.$javascript, $withAttrsResult);
    }

    public function testdisplaySubmitWithCustomCallback()
    {
        self::assertInstanceOf(HCaptcha::class, $this->captcha);

        $withAttrs = '<button data-theme="light" class="h-captcha 123" data-callback="onSubmitCustomCallback" data-sitekey="{site-key}"><span>submit123</span></button>';

        $withAttrsResult = $this->captcha->displaySubmit('test-custom', 'submit123',
            ['data-theme' => 'light', 'class' => '123', 'data-callback' => 'onSubmitCustomCallback']);
        self::assertEquals($withAttrs, $withAttrsResult);
    }
}
