<?php

namespace lib\MovieRepo\tests;

abstract class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->data_dir = '/tmp/movie_repo';
    }

    function get_test_fetcher()
    {
        // cache, zeby nie spowalniac testow pobieraniem z zewnetrznych serwisow
        $fetcher = new \lib\Fetcher\CacheCurlFetcher($this->data_dir, 3600);

        return $fetcher;
    }

    function gladiator_data()
    {
        return array(
            "title"=> "Gladiator",
            "title_en"=> "Gladiator",
            "year"=> "2000",
            "length"=> "155",
            "country"=> "USA",
            "premiere_world"=> "2000-05-01",
            "premiere_poland"=> "2000-07-14",
            "director"=> "Ridley Scott",
            "script"=> "David Franzoni",
            "cast"=> array(
                0=> "Russell Crowe",
                1=> "Joaquin Phoenix",
                2=> "Connie Nielsen",
            )
        );
    }

    function test_gladiator()
    {
        $url = $this->urls['gladiator'];
        $movie = $this->get_parser()->get($url);
        $former = $this->gladiator_data();

        $this->assertSame($movie['title'], $former['title'], 'title');
        if (isset($movie['title_en']) && $movie['title_en'])
        {
            $this->assertSame($movie['title_en'], $former['title_en'], 'title_en');
        }

        $this->assertSame($movie['year'], $former['year'], 'year');
        $this->assertSame($movie['length'], $former['length'], 'length');
        $this->assertContains($former['country'], (string)$movie['country'], 'country');
        $this->assertSame($movie['premiere_poland'], $former['premiere_poland'], 'premiere_poland');
        $this->assertSame($movie['director'], $former['director'], 'director');
        $this->assertSame($movie['script'], $former['script'], 'script');
        $this->assertContains('http:', (string)$movie['poster'], 'poster'); 

        //niektore nie maja
        if (isset($movie['premiere_world']) && $movie['premiere_world'])
        {
            $this->assertSame($movie['premiere_world'], $former['premiere_world'], 'premiere_world');
        }

        $this->assertGreaterThan(100, strlen($movie['plot']), 'plot');
    }

    function test_gladiator_cast()
    {
        $url = $this->urls['gladiator'];
        $movie = $this->get_parser()->get($url);
        $former = $this->gladiator_data();

        foreach ($former['cast'] as $item)
        {
            $this->assertContains($item, $movie['cast'], 'cast');
        }
    }

    function test_get_image()
    {
        $url = $this->urls['gladiator'];
        $parser = $this->get_parser();
        $movie = $parser->get($url);
        $img_file = $parser->save_image($movie['poster'], $this->data_dir);
        $this->assertTrue(file_exists($img_file));
    }

    /**
     * @dataProvider provider_get_movie_url
     */
    function test_get_movie_url($title, $year, $url)
    {
        $parser = $this->get_parser();
        $this->assertSame($url, $parser->get_movie_url($title, $year, 70));
    }

}
