<?php

namespace lib\MovieRepo;

class Filmweb extends Parser
{
    protected $main_url = 'http://www.filmweb.pl';

    public function extract_data($url)
    {
        $html = $this->fetcher->load($url);
        $html = $this->strip_script($html);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = @$dom->loadHTML($html);
        libxml_clear_errors();

        $xpath =  '//div[@class="filmTitle"]//a';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['title'] = $node->textContent;

        $xpath =  '//div[@class="filmTitle"]//h2';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if ($node)
        {
            $data['title_en'] =  $node->textContent;
        }

        $xpath =  '//span[@id="filmYear"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if (preg_match('#(\d+)+#', $node->textContent, $matches))
        {
            $data['year'] = $matches[1];
        }

        $xpath =  '//div[@class="filmPlot"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['plot'] = $node->textContent;

        $xpath =  '//div[@class="filmTime"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if (preg_match('#(\d+)+ godz\. (\d+) min#', $node->textContent, $matches))
        {
            $data['length'] = 60*$matches[1] + $matches[2];
        }

        $xpath =  '//div[@class="filmInfo"]//th[contains(.,"gatunek")]/following-sibling::node()[1]//li';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['genre'] = $node->textContent;

        $xpath =  '//div[@class="filmInfo"]//th[contains(.,"produkcja")]/following-sibling::node()[1]//li';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['country'] = $node->textContent;

        $xpath =  '//div[@class="filmInfo"]//span[@id="filmPremiereWorld"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['premiere_world'] = $node->textContent;

        $xpath =  '//div[@class="filmInfo"]//span[@id="filmPremierePoland"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['premiere_poland'] = $node->textContent;

        //nie obsluguje pl znakow
        $xpath =  '//div[@class="filmInfo"]//th[contains(.,"yseria")]/following-sibling::node()[1]//li';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['director'] = $node->textContent;

        $xpath =  '//div[@class="filmInfo"]//th[contains(.,"scenariusz")]/following-sibling::node()[1]//li';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['script'] = $node->textContent;

        //obsada
        $xpath =  '//div[contains(@class, "filmCastBox")]//li/span';
        $list = $this->xpath_query($dom, $xpath);
        foreach ($list as $node)
        {
            $data['cast'][] = $node->textContent;
        }

        //poster
        $xpath =  '//div[@class="posterLightbox"]//a[contains(@class, "film_mini")]'; // najpierw pobiermay du¿± fotkê
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if ($node)
        {
            $data['poster'] = $node->getAttribute('href');
        }
        else
        {
            $xpath =  '//div[@class="posterLightbox"]//img';
            $node = $this->xpath_query($dom, $xpath)->item(0);
            if ($node)
            {
                $data['poster'] = $node->getAttribute('src');
            }
        }

        return $data;
    }

    public function get_movie_url($title, $year, $similarity=70)
    {
        $url = $this->main_url . "/search/film?q=";
        $url .= urlencode(self::simplify_title($title));
        $html = $this->fetcher->load($url);
        $html = $this->strip_script($html);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = @$dom->loadHTML($html);
        libxml_clear_errors();

        $xpath =  '//div[@id="searchResult"]//h3/a';
        $dx = new \DOMXPath($dom);
        $list = $dx->query($xpath);

        foreach ($list as $item)
        {
            $tmp = $item->textContent;

            // pobierz i wytnij rok
            $year_node = $dx->query("./following-sibling::span[1]", $item)->item(0);
            $candidate['year'] = (int)trim($year_node->textContent, '()');

            $tmp = preg_replace('#\(.*\)#', '', $tmp);
            // usun tytul angielski
            $tmp = preg_replace('#(/.*)#', '', $tmp);
            $candidate['title'] = trim($tmp);
            $candidate['url'] = $this->main_url . $item->getAttribute('href');

            $candidates[] = $candidate;
        }

        return $this->match_candidates($title, $year, $similarity, $candidates);
    }
}
