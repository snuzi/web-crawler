<?php

include __DIR__ . '/vendor/autoload.php';

use sabri\Extractor\DBLinkStorage;
use sabri\Extractor\LinkExtractor;

$baseurl = 'https://www.bbc.co.uk/food';

$linkStorage = new DBLinkStorage(__DIR__ . '/resources/database');
$extractor = new LinkExtractor($baseurl, $linkStorage);
$extractor->run();

