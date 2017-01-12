<?php

namespace Denismitr\Sitemap;

class LinksCollection
{
    /**
     * Array of links
     *
     * @var array
     */
    private $collection = [];

    /**
     * The levels of domain pages must have priorities
     *
     * @var array
     */
    private $levels = [];

    /**
     * Domain full url
     *
     * @var string
     */
    private $domain;

    /**
     * Number of chars in domain name
     *
     * @var [type]
     */
    private $domainLength;

    /**
     * The minimal priority: no metter how deep a page is
     *
     * @var float
     */
    private $minimalPriority;

    /**
     * [$defaulUpdatePeriod]
     *
     * @var string
     */
    protected $defaulUpdatePeriod = 'monthly';

    /**
     * Constructor
     *
     * @param string $domain          [description]
     * @param array  $levels          [description]
     * @param float $minimalPriority [description]
     */
    public function __construct(string $domain, array $levels, float $minimalPriority, string $defaulUpdatePeriod)
    {
        $this->domain = $domain;

        $this->levels = $levels;

        $this->domainLength = strlen( $this->domain );

        $this->minimalPriority = $minimalPriority;

        $this->$defaulUpdatePeriod = $defaulUpdatePeriod;
    }


    public function exists($link)
    {
        foreach ($this->collection as $aLink) {
            if ( $aLink->eqauls($link) ) return true;
        }

        return false;
    }


    public function add($link)
    {
        if ( ! empty($link) ) {
            $level = $this->calculateLevel($link);

            $this->collection[] = new ALink(
                $link,
                $this->calculatePriority($level),
                $this->getUpdateFrequency($level)
            );

            return $this;
        }

        throw new \InvalidArgumentException;
    }


    private function calculateLevel($link)
    {
        $link = substr($link, $this->domainLength);

        //If domain is give return 0 level
        if ( empty($link) ) return 0;

        $levels = explode('/', rtrim($link, '/'));

        return count($levels);
    }


    private function calculatePriority($level)
    {
        return $this->levels[$level]['priority'] ?? $this->minimalPriority;
    }


    private function getUpdateFrequency($level)
    {
        return $this->levels[$level]['updates'] ?? $this->defaulUpdatePeriod;
    }


    public function toXmlFile(string $file, $xmlns = null, $xsi = null, $schemaLocation = null)
    {
        $xml = new SitemapXML($xmlns, $xsi, $schemaLocation);

        foreach ($this->collection as $aLink) {
            $xml->addNode($aLink->link(), $aLink->priority(), $aLink->updates());
        }

        $xml->persist($file);
    }
}