<?php

namespace lib\MovieRepo\tests;

use lib\MovieRepo;

class FilmwebTest extends ParserTest
{
    protected $urls = array(
        'gladiator' => 'http://www.filmweb.pl/Gladiator'
    );

    function get_parser()
    {
        $fetcher = $this->get_test_fetcher();
        return new MovieRepo\Filmweb($fetcher);
    }

    function provider_get_movie_url()
    {
        return array(
            array('Osaczony',2005,'http://www.filmweb.pl/Osaczony'),
            array('Historie kuchenne',2003,'http://www.filmweb.pl/Historie.Kuchenne'),
            array('American Pie: Zjazd absolwentow',2012,'http://www.filmweb.pl/film/American+Pie%3A+Zjazd+absolwent%C3%B3w-2012-593914'),
            array('Czysciciel',2007,'http://www.filmweb.pl/Czysciciel'),
            array('Wszyscy Swieci',2002,'http://www.filmweb.pl/film/Wszyscy+%C5%9Bwi%C4%99ci-2002-34701'),
            array('Smiertelnie proste',1984,'http://www.filmweb.pl/film/%C5%9Amiertelnie+proste-1984-1071'),
            array('Mud',2012, NULL),
        );
    }

}
