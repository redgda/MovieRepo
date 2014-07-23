<?php

namespace lib\Fetcher;

class CacheCurlFetcher extends CurlFetcher
{
    /**
     * @param string $cache_dir 
     * @param int $cache_time seconds (0, cache never expires)
     */
    public function __construct($cache_dir='', $cache_time=0)
    {
        parent::__construct();

        $this->cache_time = (int)$cache_time;

        $cache_dir = rtrim($cache_dir, '/') . '/';
        if (is_writable($cache_dir))
        {
            $this->cache_dir = $cache_dir;
        }
        else
        {
            Throw new \Exception('Not writable cache_dir: '.$cache_dir);
        }
    }

    public function load($url)
    {
        //@todo extract url validation
        if (empty($url))
        {
            Throw new \Exception('Empty url');
        }

        if ($this->cached($url))
        {
            $content = $this->load_cache($url);
        }
        else
        {
            $content = parent::load($url);
            if (!empty($content))
            {
                $this->save_cache($url, $content);
            }
        }

        return $content;
    }

    public function cached($url)
    {
        $cache_file = $this->cache_filepath($url);
        if (!file_exists($cache_file))
        {
            return false;
        }

        if ($this->cache_time && (filemtime($cache_file) + $this->cache_time < time()))
        {
            return false;
        }

        return true;
    }

    public function save_cache($url, $content)
    {
        $saved = file_put_contents($this->cache_filepath($url), $content);
        return $saved;
    }

    private function cache_filepath($url)
    {
        $filename = str_replace( array('/', ':', '.', '?', '&'), '_', $url);
        $filepath = $this->cache_dir . $filename;
        return $filepath;
    }

    public function load_cache($url)
    {
        return file_get_contents($this->cache_filepath($url));
    }
}
