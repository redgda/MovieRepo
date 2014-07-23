<?php

namespace lib\MovieRepo;

abstract class Parser
{
    public static function get_parser_name_from_url($url)
    {
        $hostname = parse_url($url, PHP_URL_HOST);
        if (preg_match('#(\w+\.)*(\w+)\.\w+#', $url, $matches))
        {
            return $matches[2];
        }
        return false;
    }

    public static function strip_script($html)
    {
        $new = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        return $new; 
    }

    public static function xpath_query($dom, $xpath)
    {
        $dx = new \DOMXPath($dom);
        $list = $dx->query($xpath);
        return $list;
    }

    function __construct($fetcher=null)
    {
        if ($fetcher)
        {
            $this->set_fetcher($fetcher);
        }
    }

    public function set_fetcher(\lib\fetcher\FetcherInterface $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    function get($url)
    {
        $this->before($url);

        $data = $this->extract_data($url);
        $data['source_url'] = $url;
        $data['parser_name'] = self::get_parser_name_from_url($url);

        $data = $this->after($data);


        return $data;
    }

    function before($url)
    {
        // uproszczona walidacja bo polskie znaki w url nie przechodza przez filter var
        if (! preg_match('#^http://(\w).(\w)#', $url))
        {
            //@todo
            Throw new \Exception("Invalid URL ($url)");
        }
    }

    function after($data)
    {
        array_walk_recursive($data, 
            function(&$item) { 
                $item = trim($item); 
                //@todo
                //$item = iconv('UTF-8', 'ISO-8859-2//IGNORE', $item);
            }
        );

        return $data;
    }

    public function save_image($url, $path)
    {
        $content = $this->fetcher->load($url);
        if (!$content)
        {
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME);
        $info = $finfo->buffer($content);
        preg_match('#image/(.*);#', $info, $matches);
        $extension = $matches[1] ?: 'jpg';
        $file = $path.md5($url).".$extension";
        file_put_contents($file, $content);

        return $file;
    }

    public static function simplify_title($title)
    {
        $title = self::replace_polish($title);
        $title = trim(strtolower($title));
        return $title;
    }

    public function match_candidates($title, $year, $similarity, $candidates)
    {
        if (!$candidates)
        {
            return;
        }

        $second_process_similarity = 95;
        $title = self::simplify_title($title);

        // 1 przebieg, dokladny rok
        foreach ($candidates as $item) 
        {
            if ($item['year'] == $year)
            {
                similar_text($title, self::simplify_title($item['title']), $score);
                if ($score >= $similarity)
                {
                    return $item['url'];
                }
            }
        }

        // 2 przebieg, rok moze roznic sie o 1
        foreach ($candidates as $item)
        {
            if (abs($item['year']-$year) == 1)
            {
                similar_text($title, self::simplify_title($item['title']), $score);
                if ($score >= $second_process_similarity)
                {
                    return $item['url'];
                }
            }
        }
    }

    protected static function replace_polish($string)
    {
        $string = strtr(
            $string,
            "±æê³ñó¶¿¼¡ÆÊ£ÑÓ¦¯¬",
            "acelnoszzACELNOSZZ"
        );

        return $string;
    }

    protected static function month_from_name($name)
    {
        $shortcuts = array( 
            'st' => 1, 'lu' => 2, 'mar' => 3, 'kw' => 4, 'maj' => 5, 'cz' => 6,
            'lip' => 7, 'si' => 8, 'wr' => 9, 'pa' => 10, 'lis' => 11, 'gr' => 12
        );

        return isset($shortcuts[substr($name, 0, 2)]) ? $shortcuts[substr($name, 0, 2)] : $shortcuts[substr($name, 0, 3)];
    }
}
