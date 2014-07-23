<?php

namespace lib\MovieRepo\tests;

use \lib\MovieRepo;

class PortalfilmowyTest extends ParserTest
{
    protected $urls = array(
        'gladiator' => 'http://www.portalfilmowy.pl/film,2786,1,Gladiator-.html'
    );

    function get_parser()
    {
        $fetcher = $this->get_test_fetcher();
        return new MovieRepo\Portalfilmowy($fetcher);
    }

    function provider_get_movie_url()
    {
        return array(
            array('Reich', 2001, 'http://www.portalfilmowy.pl/film,13345,1,Reich.html'),
            array('PitBull',2005,'http://www.portalfilmowy.pl/film,12883,1,Pitbull.html'),
            array('Krwawe walentynki',2009,'http://www.portalfilmowy.pl/film,13425,1,Krwawe-walentynki.html'),
            array('Step Up 3',2010,'http://www.portalfilmowy.pl/film,167,1,Step-Up-3D.html'),
            array('Ze zycie ma sens',2000,'http://www.portalfilmowy.pl/film,13355,1,Ze-zycie-ma-sens.html'),
            array('Zakochany aniol',2005,'http://www.portalfilmowy.pl/film,13296,1,Zakochany-aniol.html'),
            array('Porozmawiaj z nia',2002,'http://www.portalfilmowy.pl/film,3732,1,Porozmawiaj-z-nia.html'),
            array('Przeboje i podboje',2000,'http://www.portalfilmowy.pl/film,17148,1,Przeboje-i-podboje.html'),
            array('Julie i Julia',2009,'http://www.portalfilmowy.pl/film,13102,1,Julie-i-Julia.html'),
            array('Stalingrad',2013,'http://www.portalfilmowy.pl/film,30356,1,Stalingrad.html'),
        );
    }

    function test_stalingrad()
    {
        $url = 'http://www.portalfilmowy.pl/film,30356,1,Stalingrad.html';
        $movie = $this->get_parser()->get($url);
        $this->assertSame('Stalingrad', $movie['title']);
        $this->assertSame('Rosja', $movie['country']);
    }
}
