Recursively extract all website inbound links

###Setup
`composer install`

### Run
```php
$baseurl = 'https://www.bbc.co.uk/food';

$linkStorage = new DBLinkStorage(__DIR__ . '/resources/database');
$extractor = new LinkExtractor($baseurl, $linkStorage);
$extractor->run();
```

###Access extracted links
Check [/rakibtg/SleekDB](https://github.com/rakibtg/SleekDB) documentation how to make queries


###Save links in a different storage
There is a build in storage `DBLinkStorage` based on NoSql database [/rakibtg/SleekDB](https://github.com/rakibtg/SleekDB)   for this library but you can implement a different storage by implementing LimkstorageInterface

###Run tests
To do..
