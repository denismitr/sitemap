<?php

namespace Denismitr\Sitemap;

class SitemapXML
{
    private $xmlns;
    private $xsi;
    private $schemaLocation;

    private $xml = '';

    public function __construct($xmlns, $xsi, $schemaLocation)
    {
        $this->xmlns = $xmlns ?: "http://www.sitemaps.org/schemas/sitemap/0.9";
        $this->xsi = $xsi ?: "http://www.w3.org/2001/XMLSchema-instance";
        $this->schemaLocation = $schemaLocation ?: "http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd";

        $this->begin();
    }


    public function addNode($location, $priority, $updates)
    {
        $this->xml .= "<url>\n";
        $this->xml .= "<loc>{$location}</loc>\n";
        $this->xml .= "<changefreq>{$updates}</changefreq>\n";
        $this->xml .= "<priority>{$priority}</priority>\n";
        $this->xml .= "</url>\n";
    }


    public function persist($file)
    {
        $this->end();

        file_put_contents($file, $this->xml, LOCK_EX);
    }


    private function begin()
    {
        $this->xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        $this->xml .= "<urlset xmlns=\"{$this->xmlns}\" xmlns:xsi=\"{$this->xsi}\" xsi:schemaLocation=\"{$this->schemaLocation}\">\n";
    }


    private function end()
    {
        $this->xml .= "</urlset>\n";
    }
}