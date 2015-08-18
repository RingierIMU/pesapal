<?php namespace Ericmuigai\Pesapal\Oauth;

use Oneafricamedia\Core\Services\PaymentService;
use OAuthException;
use Config;
use Input;
use Log;

/**
 * Class Ipnlisten
 * @package Ericmuigai\Pesapal\Oauth
 */
class Ipnlisten
{
    public function __construct(
        $consumer_key,
        $consumer_secret,
        $status_request_api
    ) {
        // Parameters sent to you by PesaPal IPN
        $pesapalNotification = Input::get('pesapal_notification_type');
        $pesapalTrackingId = Input::get('pesapal_transaction_tracking_id');
        $pesapal_merchant_reference = Input::get('pesapal_merchant_reference');
        $signature_method = new OAuthSignatureMethodHmacSha1();
        $paymentService = new PaymentService;

        if ($pesapalNotification == "CHANGE" && $pesapalTrackingId!='') {

            $token = $params = $statusType = $orderStatus = null;
            $consumer = new OAuthConsumer($consumer_key, $consumer_secret);

            //get transaction status
            $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $status_request_api, $params);
            $request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
            $request_status->set_parameter("pesapal_transaction_tracking_id", $pesapalTrackingId);
            $request_status->sign_request($signature_method, $consumer, $token);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_status);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            if (defined('CURL_PROXY_REQUIRED')) {
                if (CURL_PROXY_REQUIRED == 'True') {
                    $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
                }
            }
            if (($response = curl_exec($ch)) == false) {
                throw new OAuthException(sprintf('PesaPay: %s (%d)', curl_error($ch), curl_errno($ch)));
            } else {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $raw_header = substr($response, 0, $header_size - 4);
                $headerArray = explode("\r\n\r\n", $raw_header);
                $header = $headerArray[count($headerArray) - 1];

                //transaction status
                $elements = preg_split("/=/", substr($response, $header_size));

                $paymentService->respondToPaymentNotification(
                    $elements,
                    $pesapal_merchant_reference,
                    $pesapalNotification,
                    $pesapalTrackingId
                );

                curl_close($ch);
            }

        }
    }
}
