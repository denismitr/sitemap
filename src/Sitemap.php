<?php

namespace Denismitr\Sitemap;

use Denismitr\Sitemap\LinksCollection;
use GuzzleHttp\Client;

class Sitemap
{
    /**
     * Domain full url
     *
     * @var string
     */
    private $domain;

    /**
     * The levels of domain pages must have priorities
     *
     * @var array
     */
    private $levels = [];

    /**
     * The minimal priority: no metter how deep a page is
     *
     * @var float
     */
    private $minimalPriority = 0.64;

    /**
     * [$defaulUpdatePeriod]
     *
     * @var string
     */
    private $defaulUpdatePeriod = 'monthly';

    /**
     * Array of pages that must be visited
     *
     * @var array
     */
    protected $pagesToVisit = [];

    /**
     * Pages that have been already visited
     *
     * @var array
     */
    private $visited = [];


    /**
     * Do not parse these routes
     *
     * @var array
     */
    protected $excluded = [];

    /**
     * Allowed
     * @var [type]
     */
    protected $allowedTypes = [
        'php',
        'html',
        'htm',
        '/'
    ];

    /**
     * Stores LinksCollection Object
     *
     * @var [LinksCollection]
     */
    private $links;


    public function __construct(string $domain)
    {
        $this->pagesToVisit[] = $this->domain = $this->normalizeLink($domain);
    }


    public function generate()
    {
        $this->links = new LinksCollection($this->domain, $this->levels, $this->minimalPriority, $this->defaulUpdatePeriod);

        $this->crawl();

        return $this->links;
    }


    public static function forDomain(string $domain)
    {
        return new static($domain);
    }

    /**
     * Set priority for a domain level
     *
     * @param int   $level    [description]
     * @param float $priority [description]
     */
    public function setLevel(int $level, float $priority, string $updates)
    {
        $this->levels[$level]['priority'] = $priority;
        $this->levels[$level]['updates'] = $updates;

        return $this;
    }


    public function setExcluded(array $excluded = [])
    {
        $this->excluded = $excluded;

        return $this;
    }


    public function setMinimalProirity($priority)
    {
        $this->minimalPriority = $priority;

        return $this;
    }


    public function setDefaultUpdatePeriod($period)
    {
        $this->defaulUpdatePeriod = $period;

        return $this;
    }


    /**
     * Start crawling
     *
     * @return [type] [description]
     */
    private function crawl()
    {
        while ( ! empty($this->pagesToVisit) ) {
            $this->visitPage(array_shift($this->pagesToVisit));
        }
    }

    /**
     * Visit a page and get body
     *
     * @param  string $page [description]
     * @return [type]       [description]
     */
    private function visitPage(string $page)
    {
        $html = $this->call($page);

        $this->visited[] = $page;
        $this->links->add($page);

        $this->collectLinksFromHtml($html);
    }

    /**
     * Get html code via get request
     *
     * @param  string $url
     * @return string
     */
    private function call($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $data = curl_exec($ch);
        $timestamp = curl_getinfo($ch, CURLINFO_FILETIME);
        curl_close($ch);

        return $data;
    }

    /**
     * Parse all links all the page and add them to $pagesToVisit array
     *
     * @param  string $html
     * @return void
     */
    private function collectLinksFromHtml(string $html)
    {
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

        if (preg_match_all("/{$regexp}/siU", $html, $matches)) {
            if ( isset($matches[2]) && ! empty($matches[2]) ) {
                $links = $matches[2];

                foreach ($links as $href) {
                    $href = $this->normalizeLink($href);

                    if ( ! $this->belongsToDomain($href) ) continue;

                    if ( $this->inExcluded($href) ) continue;

                    if ( ! $this->ofAllowedType($href) ) continue;

                    if ( $this->containsWrongSymbols($href) ) continue;

                    if ( ! $this->isVisited($href) && ! in_array($href, $this->pagesToVisit) ) {
                        $this->pagesToVisit[] = $href;
                    }
                }
            }
        }
    }

    /**
     * Check if the link has already been visited
     *
     * @param  string  $link
     * @return boolean
     */
    private function isVisited($link)
    {
        return in_array($link, $this->visited);
    }

    /**
     * Check if route is of allowed type
     *
     * @param  [string] $link
     * @return [boolean]
     */
    private function ofAllowedType($link)
    {
        foreach ($this->allowedTypes as $type) {
            $len = strlen($type);

            if (substr($link, -$len) === $type) return true;
        }

        return false;
    }

    /**
     * Normalize a link
     *
     * @param  string $link
     * @return string
     */
    private function normalizeLink($link)
    {
        // We exclude links without extensions
        $link = rtrim($link, '/');

        //if link has extension .html .htm no trailing slah needed
        if ( $this->ofAllowedType($link) ) {
            return $link;
        }

        //add trailing slash
        return $link . '/';
    }


    private function belongsToDomain($link)
    {
        return strpos($link, $this->domain) === 0;
    }

    /**
     * Check if the link is in excluded array
     *
     * @param  [type] $link
     * @return [type]
     */
    private function inExcluded($link)
    {
        return in_array($link, $this->excluded);
    }

    /**
     * Query strings and hashes are excluded 'a priore'
     *
     * @param  [string] $link
     * @return [boolean]
     */
    private function containsWrongSymbols($link)
    {
        return ! (strpos($link, '?') === false && strpos($link, '#') === false);
    }
}
