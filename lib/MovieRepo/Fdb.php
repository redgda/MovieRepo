<?php

namespace lib\MovieRepo;

class Fdb extends Parser
{
    protected $main_url = 'http://fdb.pl';

    public function extract_data($url)
    {
        $dom = new \DOMDocument();
        $html = $this->fetcher->load($url);
        $html = $this->strip_script($html);

        libxml_use_internal_errors(true);
        $loaded = @$dom->loadHTML($html);
        libxml_clear_errors();

        $xpath =  '//h1[@id="movie-title"]/a';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['title'] = $node->textContent;

        $xpath =  '//h2[@class="after-title"]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['title_en'] = $node->textContent;

        $xpath =  '//h1[@id="movie-title"]/small/a';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if (preg_match('#(\d+)+#', $node->textContent, $matches))
        {
            $data['year'] = $matches[1];
        }

        //usuwa tekst zmien opis
        $xpath =  '//ul[@class="sections"]/li[@id="description"]/p';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if ($node)
        {
            $span = $node->getElementsByTagName('span')->item(0);
            $node->removeChild($span);
        }
        $data['plot'] = $node->textContent;
        if (strstr($data['plot'], 'opis dystrybutora'))
        {
            $data['plot_from_distributor'] = true;
            $data['plot'] = str_replace('(opis dystrybutora)', '', $data['plot']);
            $data['plot'] = trim($data['plot']) .' [opis dystrybutora]';
        }

        $xpath =  '//li[@id="summary"]/ul/li[1]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['genre'] = $node->textContent;

        $xpath =  '//li[@id="summary"]/ul/li[2]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if (preg_match('#(\d+-\d+-\d+)+#', $node->textContent, $matches))
        {
            $data['premiere_poland'] = $matches[1];
        }

        $xpath =  '//li[@id="summary"]/ul/li[3]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['country'] = $node->textContent;

        $xpath =  '//li[@id="summary"]/ul/li[4]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['length'] = (int)$node->textContent;

        //nie obsluguje pl znakow
        $xpath =  '//li[@id="crew_overview"]//dt[contains(.,"yseria")]/following-sibling::node()/a';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['director'] = $node->textContent;

        $xpath =  '//li[@id="crew_overview"]//dt[contains(.,"Scenariusz")]/following-sibling::node()/a';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        $data['script'] = $node->textContent;

        //obsada
        $xpath =  '//li[@id="cast"]//td[@class="name"]/a';
        $list = $this->xpath_query($dom, $xpath);
        foreach ($list as $node)
        {
            $data['cast'][] = $node->textContent;
        }

        $xpath =  '//img[contains(@class,"gfx-poster-new")]';
        $node = $this->xpath_query($dom, $xpath)->item(0);
        if ($node)
        {
            $data['poster'] = $node->getAttribute('src');
        }

        return $data;
    }

    public function get_movie_url($title, $year, $similarity_limit=70)
    {
        $url = $this->main_url . "/szukaj?utf8=%E2%9C%93&query=";
        $url .= urlencode(self::simplify_title($title));
        $html = $this->fetcher->load($url);
        if ($html)
        {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML($html);
            libxml_clear_errors();
            $dx = new \DOMXPath($dom);
            $xpath =  '//ol[@class="results"]/li//div[@class="content"]/p/a';
            $list = $dx->query($xpath);

            foreach ($list as $item)
            {
                $tmp = $item->textContent;

                // wyciagnij i wytnij rok
                if (preg_match('#\((\d+).*\)#', $tmp, $matches))
                {
                    $candidate['year'] = (int)$matches[1];
                    $tmp = preg_replace('#\(.*\)#', '', $tmp);

                    $candidate['title'] = $tmp;
                    $candidate['url'] = $item->getAttribute('href');
                    $candidates[] = $candidate;
                }
            }

            return $this->match_candidates($title, $year, $similarity_limit, $candidates);
        }
    }
}
