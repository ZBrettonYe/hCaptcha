<?php

namespace ZBrettonYe\HCaptcha;

use GuzzleHttp\Client;
use http\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class HCaptcha
{
    public const CLIENT_API = 'https://hcaptcha.com/1/api.js';
    public const VERIFY_URL = 'https://hcaptcha.com/siteverify';

    /**
     * The hCaptcha secret key.
     *
     * @var string
     */
    private $secret;

    /**
     * The hCaptcha sitekey key.
     *
     * @var string
     */
    private $sitekey;

    /**
     * @var Client
     */
    private $http;

    /**
     * The cached verified responses.
     *
     * @var array
     */
    private $verifiedResponses = [];

    /**
     * HCaptcha.
     *
     * @param  string  $secret
     * @param  string  $sitekey
     * @param  array  $options
     */
    public function __construct($secret, $sitekey, $options = [])
    {
        $this->secret = $secret;
        $this->sitekey = $sitekey;
        $this->http = new Client($options);
    }

    /**
     * @see display()
     */
    public function displayWidget($attributes = []): string
    {
        return $this->display($attributes);
    }

    /**
     * Render HTML captcha.
     *
     * @param  array  $attributes
     *
     * @return string
     */
    public function display($attributes = []): string
    {
        $attributes = $this->prepareAttributes($attributes);

        return '<div '.$this->buildAttributes($attributes).'></div>';
    }

    /**
     * Prepare HTML attributes and assure that the correct classes and attributes for captcha are inserted.
     *
     * @param  array  $attributes
     *
     * @return array
     */
    protected function prepareAttributes(array $attributes): array
    {
        $attributes['data-sitekey'] = $this->sitekey;
        if (! isset($attributes['class'])) {
            $attributes['class'] = '';
        }
        $attributes['class'] = trim('h-captcha '.$attributes['class']);

        return $attributes;
    }

    /**
     * Build HTML attributes.
     *
     * @param  array  $attributes
     *
     * @return string
     */
    protected function buildAttributes(array $attributes): string
    {
        $html = [];

        foreach ($attributes as $key => $value) {
            $html[] = "$key=\"$value\"";
        }

        return implode(' ', $html);
    }

    /**
     * Display a Invisible hCaptcha by embedding a callback into a form submit button.
     *
     * @param  string  $formIdentifier  the html ID of the form that should be submitted.
     * @param  string  $text  the text inside the form button
     * @param  array  $attributes  array of additional html elements
     *
     * @return string
     */
    public function displaySubmit($formIdentifier, $text = 'submit', $attributes = []): string
    {
        $javascript = '';
        if (! isset($attributes['data-callback'])) {
            $functionName = 'onSubmit'.str_replace(['-', '=', '\'', '"', '<', '>', '`'], '', $formIdentifier);
            $attributes['data-callback'] = $functionName;
            $javascript = sprintf('<script>function %s(){document.getElementById("%s").submit();}</script>', $functionName, $formIdentifier);
        }

        $attributes = $this->prepareAttributes($attributes);

        $button = sprintf('<button %s><span>%s</span></button>', $this->buildAttributes($attributes), $text);

        return $button.$javascript;
    }

    /**
     * Render js source
     *
     * @param  null  $lang
     * @param  bool  $callback
     * @param  string  $onLoadClass
     *
     * @return string
     */
    public function renderJs($lang = null, $callback = false, $onLoadClass = 'onloadCallBack'): string
    {
        return '<script src="'.$this->getJsLink($lang, $callback, $onLoadClass).'" async defer></script>'."\n";
    }

    /**
     * Get hCaptcha js link.
     *
     * @param  string  $lang
     * @param  boolean  $callback
     * @param  string  $onLoadClass
     *
     * @return string
     */
    public function getJsLink($lang = null, $callback = false, $onLoadClass = 'onloadCallBack'): string
    {
        $client_api = static::CLIENT_API;
        $params = [];

        if ($callback) {
            $this->setCallBackParams($params, $onLoadClass);
        }
        if ($lang) {
            $params['hl'] = $lang;
        }

        return $client_api.'?'.http_build_query($params);
    }

    /**
     * @param $params
     * @param $onLoadClass
     */
    protected function setCallBackParams(&$params, $onLoadClass): void
    {
        $params['render'] = 'explicit';
        $params['onload'] = $onLoadClass;
    }

    /**
     * Verify hCaptcha response by Symfony Request.
     *
     * @param  Request  $request
     *
     * @return bool
     */
    public function verifyRequest(Request $request): bool
    {
        return $this->verifyResponse($request->get('h-captcha-response'), $request->getClientIp());
    }

    /**
     * Verify hCaptcha response.
     *
     * @param  string  $response
     * @param  string  $clientIp
     *
     * @return bool
     */
    public function verifyResponse($response, $clientIp = null): bool
    {
        if (empty($response)) {
            return false;
        }

        // Return true if response already verfied before.
        if (in_array($response, $this->verifiedResponses, true)) {
            return true;
        }

        $verifyResponse = $this->sendRequestVerify([
            'secret' => $this->secret,
            'response' => $response,
            'remoteip' => $clientIp,
        ]);

        if (isset($verifyResponse['success']) && $verifyResponse['success'] === true) {
            // Check score if it's enabled.
            $isScoreVerificationEnabled = config('HCaptcha.score_verification', false);

            if ($isScoreVerificationEnabled) {
                if (! array_key_exists('score', $verifyResponse)) {
                    throw new RuntimeException('Score Verification is an exclusive Enterprise feature! Moreover, make sure you are sending the remoteip in your request payload!');
                }

                if ($verifyResponse['score'] > config('HCaptcha.score_threshold', 0.7)) {
                    return false;
                }
            }

            // A response can only be verified once from hCaptcha, so we need to
            // cache it to make it work in case we want to verify it multiple times.
            $this->verifiedResponses = $response;

            return true;
        }

        return false;
    }

    /**
     * Send verify request.
     *
     * @param  array  $query
     *
     * @return array
     */
    protected function sendRequestVerify(array $query = []): array
    {
        $response = $this->http->request('POST', static::VERIFY_URL, [
            'form_params' => $query,
        ]);

        return json_decode($response->getBody(), true);
    }
}
