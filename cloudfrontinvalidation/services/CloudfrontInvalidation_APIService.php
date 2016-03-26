<?php
/**
 * CloudFront invalidation plugin for Craft CMS
 *
 * CloudfrontInvalidation Service
 *
 * Credit to: https://github.com/subchild/CloudFront-PHP-Invalidator
 *
 * @author    Fred Carlsen
 * @copyright Copyright (c) 2016 Fred Carlsen
 * @link      http://sjelfull.no
 * @package   CloudfrontInvalidation
 * @since     1.0.0
 */

namespace Craft;

use Guzzle\Http\Message\Request;

class CloudfrontInvalidation_APIService extends BaseApplicationComponent
{

    protected $secretKey;
    private   $client;
    private   $serviceUrl;
    private   $accessKeyId;
    private   $responseCode;
    private   $distributionId;
    private   $responseMessage;

    public function init ()
    {
        parent::init();

        $plugin = craft()->plugins->getPlugin('cloudfrontinvalidation');

        if ( !$plugin ) {
            return false;
        }

        $settings = $plugin->getSettings();

        $serviceUrl     = 'https://cloudfront.amazonaws.com/';
        $distributionId = $settings['distributionId'];


        $this->distributionId = $distributionId;
        $this->serviceUrl     = $serviceUrl;

        $this->client = new \Guzzle\Http\Client();
    }


    /**
     * Invalidates object with passed key on CloudFront
     *
     * @param $keys
     *
     * @return bool|string
     * @internal param $key {String|Array} Key of object to be invalidated, or set of such keys
     */
    function invalidate ($accessKeyId, $secretKey, $keys)
    {
        $this->accessKeyId = $accessKeyId;
        $this->secretKey   = $secretKey;

        if ( !is_array($keys) ) {
            $keys = [ $keys ];
        }

        // Put together request url with versioned API
        $requestUrl = $this->serviceUrl . "2012-07-01/distribution/" . $this->distributionId . "/invalidation";

        // Options
        $options = [
            'verify' => true
        ];

        // Setup request
        $request = $this->client->post($requestUrl, [ ], null, $options);

        // Set request headers
        $this->setRequestHeaders($request);

        // Set XML body
        $body = $this->makeRequestBody($keys);
        $request->setBody($body, 'text/xml');

        try {
            $response           = $request->send();
            $this->responseCode = $response->getStatusCode();

            switch ($this->responseCode) {
                case 201:
                    $this->responseMessage = '201: Request accepted';

                    return true;
                default:
                    $this->responseMessage = $response->getStatusCode() . ': ' . $response->getReasonPhrase();

                    return false;
            }
        }
        catch (\Exception $e) {
            $this->responseCode = $e->getResponse()->getStatusCode();

            switch ($this->responseCode) {
                case 400:
                    $this->responseMessage = '400: Too many invalidations in progress. Retry in some time';

                    return false;
                case 403:
                    $this->responseMessage = '403: Forbidden. Please check your security settings.';

                    return false;
                default:
                    $this->responseMessage = $e->getResponse()->getStatusCode() . ': ' . $e->getResponse()->getReasonPhrase();

                    return false;
            }
        }
    }


    /**
     * Sets the common headers required by CloudFront API
     *
     * @param Request $request
     */
    private function setRequestHeaders ($request)
    {
        $date = gmdate("D, d M Y G:i:s T");
        $request->setHeader("Date", $date);
        $request->setHeader("Authorization", $this->generateAuthKey($date));
    }

    /**
     * Makes the request body as expected by CloudFront API
     *
     * @param $objects objects to Invalidate
     *
     * @return string
     */
    private function makeRequestBody ($objects)
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>';
        $body .= '<InvalidationBatch xmlns="http://cloudfront.amazonaws.com/doc/2012-07-01/">';
        $body .= '<Paths>';
        $body .= '<Quantity>' . count($objects) . '</Quantity>';
        $body .= '<Items>';

        foreach ($objects as $object) {
            $object = (preg_match("/^\//", $object)) ? $object : "/" . $object;
            $body .= "<Path>" . $object . "</Path>";
        }

        $body .= '</Items>';
        $body .= '</Paths>';
        $body .= "<CallerReference>" . time() . "</CallerReference>";
        $body .= "</InvalidationBatch>";

        return $body;
    }


    /**
     * Returns header string containing encoded authentication key
     *
     * @param $date
     *
     * @return string
     */
    private function generateAuthKey ($date)
    {
        $signature = base64_encode(hash_hmac('sha1', $date, $this->secretKey, true));

        return "AWS " . $this->accessKeyId . ":" . $signature;
    }


}
