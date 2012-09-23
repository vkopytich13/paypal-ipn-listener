<?php

namespace PayPal\Ipn\Request;

class cURL extends \PayPal\Ipn\Request
{
    /**
     * Should cURL follow location headers in the response
     *
     * @var bool
     */
    protected $followLocation;

    /**
     * Should cURL use SSL version 3
     *
     * @var bool
     */
    protected $forceSSLv3;

    /**
     * Create a new instance
     *
     * @throws CurlException
     */
    public function __construct()
    {
        if (!function_exists('curl_version')) {
            throw new CurlException('cURL extension is either not enabled or not installed');
        }

        parent::__construct();

        $this->followLocation = false;
        $this->forceSSLv3 = true;
    }

    /**
     * Set whether cURL will use the CURLOPT_FOLLOWLOCATION to follow any
     * location headers in the response. Defaults to false
     *
     * @param bool $followLocation
     */
    public function followLocation($followLocation)
    {
        $this->followLocation = (bool) $followLocation;
    }

    /**
     * Explicitly sets cURL to use SSL version 3. Use this if cURL
     * is compiled with GnuTLS SSL.
     *
     * @param bool $forceSSLv3
     */
    public function forceSSLv3($forceSSLv3)
    {
        $this->forceSSLv3 = (bool) $forceSSLv3;
    }

    /**
     * Sends the request to PayPal
     *
     * @throws CurlException
     */
    public function send()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getRequestUri());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encodedData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if ($this->forceSSLv3) {
            curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        }

        $responseBody = curl_exec($ch);
        $responseStatus = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        $this->response->setBody($responseBody);
        $this->response->setStatus($responseStatus);

        if ($responseBody === false or $responseStatus == '0') {
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            throw new CurlException('cURL error: [' . $errno . '] ' . $error);
        }
    }
}
