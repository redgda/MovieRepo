<?php
require_once 'bootstrap.php';

use lib\MovieRepo;
use lib\Fetcher;

$sources = array('Fdb', 'Filmweb', 'Portalfilmowy');

$fetcher = new Fetcher\CacheCurlFetcher(__DIR__ . '/data', 3600);

$search['title'] = 'gladiator';
$search['year'] = 2000;

foreach ($sources as $source) 
{
    $repo = MovieRepo\Factory::create($source);
    $repo->set_fetcher($fetcher);

    $url = $repo->get_movie_url($search['title'], $search['year']);
    $data = $repo->get($url);

    echo "source: {$url}\n";
    echo "title: {$data['title']}\n";
    echo "year: {$data['year']}\n";
    echo "plot: {$data['plot']}\n";
    echo "\n";
}

