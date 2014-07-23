<?php

namespace lib\Fetcher;

class CurlFetcher implements FetcherInterface
{
    /**
     * curl resource
     */
    protected $ch;

    public function __construct($ch = null)
    {
        if (!function_exists('curl_exec'))
        {
            Throw new \Exception('curl method not supported');
        }

        if (!$ch)
        {
            $ch = $this->get_default_handler();
        }

        $this->set_handler($ch);

    }

    public function get_default_handler()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        return $ch;
    }

    public function simulate_ff_browser()
    {
        $header[0] = "Accept: text/html,application/xhtml+xml,";
        $header[0] .= "application/xml;q=0.9,*/*;q=0.8";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 115";
        $header[] = "Accept-Charset: ISO-8859-2,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: pl,en-us;q=0.7,en;q=0.3";
        $header[] = "Pragma: ";

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt ($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.2) Gecko/20100115 Firefox/3.6' );
        curl_setopt ($this->ch, CURLOPT_ENCODING, 'gzip,deflate');
    }

    /**
     * use proxy
     *
     * example proxy list:
     * 198.50.241.160:7808
     * 208.110.83.202:7808
     * 208.110.83.202:3128
     * http://serwery-proxy.eu/lista-serwerow-proxy,ping,2
     */
    public function set_proxy($address)
    {
        curl_setopt($this->ch, CURLOPT_PROXY, $address);
    }

    public function set_handler($ch)
    {
        if (gettype($ch) != 'resource' || get_resource_type($ch) !='curl')
        {
            Throw new \Exception('not valid curl resource given');
        }

        $this->ch = $ch;
    }

    public function load($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return curl_exec($this->ch);
    }
}
