<?php namespace Ericmuigai\Pesapal\Oauth;

/**
 * Class OAuthSignatureMethod
 * @package Ericmuigai\Pesapal\Oauth
 */
class OAuthSignatureMethod
{
    public function check_signature(&$request, $consumer, $token, $signature)
    {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}
