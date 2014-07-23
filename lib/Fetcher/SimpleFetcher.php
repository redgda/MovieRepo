<?php

namespace lib\Fetcher;

class SimpleFetcher implements FetcherInterface
{
    public function load($url) 
    {
        return file_get_contents($url);
    }
}
