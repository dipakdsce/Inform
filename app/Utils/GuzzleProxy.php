<?php
/**
 * Created by PhpStorm.
 * User: krunal.s
 * Date: 15/09/16
 * Time: 2:07 PM
 */

namespace App\Utils;


use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\Promise;
use Facades\GuzzleHttp\Client;

class GuzzleProxy
{
    private $method;
    private $url;
    private $guzzleOptions;
    private $failedLastCall;

    public function __construct($method, $url, $guzzleOptions = [])
    {
        $this->method = $method;
        $this->url = $url;
        $this->guzzleOptions = $guzzleOptions;
        $this->failedLastCall = false;
    }

    public static function callConcurrent(array $guzzleProxies, $getBody = false, $encodeUTF8 = false, $jsonDecode = false)
    {
        try {
            $promises = [];
            foreach ($guzzleProxies as $key => $guzzleProxy)
            {
                $client = \GuzzleHttp::requestAsync($guzzleProxy->method, $guzzleProxy->url, $guzzleProxy->guzzleOptions);
                $promises[$key] = $client;
            }

            $results = \GuzzleHttp\Promise\unwrap($promises);
            if($getBody)
            {
                foreach ($results as $key => $result)
                {
                    $results[$key] = $result->getBody()->getContents();
                    if($encodeUTF8)
                    {
                        $results[$key] = utf8_encode($results[$key]);
                    }
                    if($jsonDecode)
                    {
                        $results[$key] = json_decode($results[$key], true);
                    }
                }
            }
            return $results;
        } catch (TransferException $e) {
            $httpStatusCode = 500;
            $message = $e->getMessage();
            if ($e->hasResponse()) {
                $httpStatusCode = $e->getResponse()->getStatusCode();
            }
            $error = [
                'status' => 'FAIL',
                'message' => $message,
                'httpStatusCode' => $httpStatusCode,
                'data' => []
            ];
            /*if(\CMDataSharer::getParam('isOfficeIp'))
            {
                $error['url'] = $this->url;
            }*/
            return $error;
        } catch (\Exception $e) {
            $error = [
                'status' => 'FAIL',
                'message' => get_class($e) . '',
                'httpStatusCode' => 500,
                'data' => []
            ];
            /*if(\CMDataSharer::getParam('isOfficeIp'))
            {
                $error['url'] = $this->url;
            }*/
            return $error;
        }
    }

    public function call($getBody = false, $encodeUTF8 = false, $jsonDecode = false)
    {
        try {
            $response = \GuzzleHttp::request($this->method, $this->url, $this->guzzleOptions);
            if($getBody)
            {
                $response = $response->getBody()->getContents();
                if($encodeUTF8)
                {
                    $response = utf8_encode($response);
                }
                if($jsonDecode)
                {
                    $response = json_decode($response, true);
                }
            }
            $this->failedLastCall = false;
            return $response;
        } catch (TransferException $e) {
//            dd($e);
            $this->failedLastCall = true;
            $httpStatusCode = 500;
            $message = $e->getMessage();
            $data = [];
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $httpStatusCode = $response->getStatusCode();
                $message = $response->getBody()->getContents();
                $data = $message;
                try {
                    if($encodeUTF8)
                    {
                        $data = utf8_encode($data);
                    }
                    if($jsonDecode)
                    {
                        $data = json_decode($data, true);
                    }
                } catch (\Exception $e) {}
            }
            $error = [
                'status' => 'FAIL',
                'message' => $message,
                'httpStatusCode' => $httpStatusCode,
                'data' => $data
            ];
            if(\CMDataSharer::getParam('isOfficeIp'))
            {
                $error['url'] = $this->url;
            }
            return $error;
        } catch (\Exception $e) {
            $this->failedLastCall = true;
            $error = [
                'status' => 'FAIL',
                'message' => get_class($e) . '',
                'httpStatusCode' => 500,
                'data' => []
            ];
            if(\CMDataSharer::getParam('isOfficeIp'))
            {
                $error['url'] = $this->url;
            }
            return $error;
        }
    }

    public function addPayload($payload, $isJson = false)
    {
        if($isJson || \Request::getContentType() === 'json')
        {
            $this->guzzleOptions['json'] = $payload;
        }
        else
        {
            $this->guzzleOptions['form_params'] = $payload;
        }
        return $this;
    }

    public function failed()
    {
        return $this->failedLastCall;
    }

    public function succeeded()
    {
        return !$this->failedLastCall;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getGuzzleOptions()
    {
        return $this->guzzleOptions;
    }

    public function setGuzzleOptions($guzzleOptions)
    {
        $this->guzzleOptions = $guzzleOptions;
        return $this;
    }

}