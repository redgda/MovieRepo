<?php

namespace lib\MovieRepo\tests;

use lib\MovieRepo;

class FdbTest extends ParserTest
{
    protected $urls = array(
        'gladiator' => 'http://fdb.pl/film/723-gladiator'
    );

    function get_parser()
    {
        $fetcher = $this->get_test_fetcher();
        return new MovieRepo\Fdb($fetcher);
    }

    function provider_get_movie_url()
    {
        return array(
            array('Amelia', 2001, 'http://fdb.pl/film/575-amelia'),
            array('Ciacho',2010,'http://fdb.pl/film/229081-ciacho'),
            array('Traffic',2000,'http://fdb.pl/film/4920-traffic'),
            array('Zywot Briana',1979,'http://fdb.pl/film/625-zywot-briana'),
            array('Oszukac przeznaczenie 3',2006,'http://fdb.pl/film/640-oszukac-przeznaczenie-3'),
            array('Przypadkowy maz',2008,'http://fdb.pl/film/76435-przypadkowy-maz'),
            array('Star Trek X: Nemesis',2002,'http://fdb.pl/film/3625-star-trek-nemesis'),
            array('To nie jest kraj dla starych ludzi',2007,'http://fdb.pl/film/6356-to-nie-jest-kraj-dla-starych-ludzi'),
            array('Faceci w czerni 3',2012,'http://fdb.pl/film/249357-faceci-w-czerni-3'),
            array('Kodeks 46',2003,'http://fdb.pl/film/1971-kodeks-46'),
            //nowe
            array('Stalingrad',2013, 'http://fdb.pl/film/379085-stalingrad'),
        );
    }

    function test_stalingrad()
    {
        $url = 'http://fdb.pl/film/379085-stalingrad';
        $movie = $this->get_parser()->get($url);
        $this->assertSame('Stalingrad', $movie['title']);
        $this->assertSame('Rosja', $movie['country']);
    }
}
