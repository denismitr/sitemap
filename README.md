# PHP Sitemap generator for websites

## Author - __Denis Mitrofanov__
[TheCollection.ru](http://thecollection.ru)

### Version 1.0.0

### Usage

This program crwawls the entire website storing links, priorities and update periods.

```php
$sitemap = new Sitemap('http://moskvado.ru/');
$sitemap
    ->setExcluded(['http://moskvado.ru/dashboard/', 'http://moskvado.ru/register/', 'http://moskvado.ru/login/'])
    ->setLevel(0, 1, 'daily')
    ->setLevel(1, 0.80, 'weekly')
    ->setLevel(2, 0.64, 'weekly')
    ->setLevel(3, 0.64, 'monthly')
    ->setDefaultUpdatePeriod('monthly')
    ->setMinimalProirity(0.5);


    $links = $sitemap->generate();
    $links->toXmlFile('sitemap.xml');
```

First you set the website you want to parse, than set the routes you want to exclude. Levels are set from 0 as domain root, to the third level, everything else should be set as minimalPriority (default is 0.64). Default update period is set to 'monthly'.

When you run $sitemap->generate() it return a LinksCollection object. Than you can persist it to actual sitemap.xml file.
By runnig $links->toXmlFile('sitemap.xml') of course you can specify full path, and not just the file name.

The output will contain xml with line for every detected link like following:

```xml
<url>
    <loc>http://moskvado.ru/kompyutery-i-it-uslugi/orgtekhnika/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.64</priority>
</url>
```

### Resources that use this generator are:
* [TheCollection.ru](http://thecollection.ru)
* [Moskvado.ru](http://moskvado.ru)
* [ruben-ovanesov.ru](http://ruben-ovanesov.ru)
* [L-Artgallery.ru](http://L-Artgallery.ru)