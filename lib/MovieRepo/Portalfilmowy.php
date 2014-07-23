<?php

namespace lib\MovieRepo;

class Portalfilmowy extends Parser
{
    protected $main_url = 'http://www.portalfilmowy.pl';

    public function extract_data($url)
    {
        $dom = new \DOMDocument();
        $html = $this->fetcher->load($url);
        $html = $this->strip_script($html);
        libxml_use_internal_errors(true);
        $loaded = @$dom->loadHTML($html);
        libxml_clear_errors();

        $xpath =  '//div[@class="movie_1"]/div';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $text = $node->textContent;

        preg_match('#\((\d+)\)$#', $text, $matches);
        $data['year'] = $matches[1];

        //wytnij rok
        $data['title'] = str_replace("({$data['year']})", '', $text);

        //slabe bo dokladna kolejnosc ale nie ma sie jak zaczepic
        $xpath = '/html/body/div/div[2]/div[9]/div[8]/div[2]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['title_en'] = $node->textContent;

        //@todo od opis filmu
        $xpath =  '//div[@id="opis"]/div[@class="movie_12"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['plot'] = $node->textContent;

        // czy opis dystrybutora
        $xpath =  '//div[@id="opis"]/div[@class="movie_12"][2]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if (strstr($node->textContent, 'opis dystrybutora'))
        {
            $data['plot_from_distributor'] = true;
        }

        //to pole bedzie bardzo czule na zmiany:
        $xpath =  '//div[contains(@class, "movie_5_1") and contains(.," min")]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $about = $node->textContent;
        preg_match('#(\d+) min#', $about, $matches);
        if ($matches[1])
        {
            $data['length'] = (int)$matches[1];
        }

        $xpath =  '//div[@class="movie_14_1" and contains(.,"KRAJ PRODUKCJI")]/following-sibling::div[1]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['country'] = $node->textContent;

        $xpath =  '//div[@class="movie_14_1" and contains(.,"GATUNEK")]/following-sibling::div[1]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['genre'] = str_replace(' ,', ',', $node->textContent);

        preg_match('#premiera PL: (\d+ \w+ \d{4})#', $about, $matches);
        if ($matches[1])
        {
            $data['premiere_poland'] = $this->format_date($matches[1]);
        }

        $xpath =  '//div[@class="movie_14_1" and contains(.,"WIAT")]/following-sibling::*[1]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['premiere_world'] = $this->format_date($node->textContent);

        $xpath =  '//div[contains(@class, "movie_5_2") and contains(.,"yseria:")]/a';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['director'] = $node->textContent;

        $xpath =  '//div[contains(@class, "movie_5_2") and contains(.,"Scenariusz")]/a[2]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['script'] = $node->textContent;

        //obsada
        $xpath =  '//div[@class="movie_10" and contains(.,"Obsada aktorska")]'
            .'/following-sibling::div[@class="movie_11"]'
            .'/div[contains(@class, "movie_11_2")]//a';
        $list = $this->xpath_query($dom, $xpath);
        $cast_count = 0;
        foreach ($list as $node)
        {
            $data['cast'][] = $node->textContent;
            // 20 pierwszych
            if (++$cast_count >= 20)
            {
                break;
            }
        }

        $xpath = '//img[@class="movie_poster"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if ($node)
        {
            $data['poster'] = $this->main_url .'/'. $node->getAttribute('src');
        }

        return $data;
    }

    function format_date($string)
    {
        if (preg_match('#(\d{2}) (.*) (\d{4})#', $string, $matches))
        {
            //$month = Lib__Utils__Datetime::month_from_name($matches[2]);
            $month = self::month_from_name($matches[2]);
            $month = sprintf("%02d", $month);

            return "{$matches[3]}-$month-{$matches[1]}";
        }

        return false;
    }

    // na przyszlosc:
    // tu jest problem z sortowaniem wynikow u zrodla (wg lat)
    // czesto dobre wyniki sa na kolejnych stronach. Trzeba albo wyciagac z paru stron
    // albo przeszukiwac angielskie tytuly.
    public function get_movie_url($title, $year, $similarity=70)
    {
        $url = $this->main_url . "/szukaj?s=";
        $url .= rawurlencode(self::simplify_title($title));
        $url .= '&so=1&p=1';

        $html = $this->fetcher->load($url);
        $html = $this->strip_script($html);

        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $loaded = @$dom->loadHTML($html);
        libxml_clear_errors();
        $xpath =  '//div[contains(@class, "f_4")]//a[contains(@href, "film,")]';
        $dx = new \DOMXPath($dom);
        $list = $dx->query($xpath);

        foreach ($list as $item)
        {
            $tmp = $item->textContent;

            //pobierz i wytnij rok
            preg_match('#\((\d+)\)$#', $tmp, $matches);
            $candidate['year'] = $matches[1];
            $tmp = trim(preg_replace('#\(.*\)#', '', $tmp));

            $candidate['title'] = $tmp;
            $candidate['url'] = $this->main_url . '/' . $item->getAttribute('href');

            $candidates[] = $candidate;
        }

        return $this->match_candidates($title, $year, $similarity, $candidates);
    }


}
