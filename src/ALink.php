<?php

namespace Denismitr\Sitemap;

class ALink
{
    private $link;
    private $priority;
    protected $updates;

    public function __construct($link, $priority, $updates)
    {
        $this->link = $link;
        $this->priority = number_format($priority, 2);
        $this->updates = $updates;
    }

    public function link()
    {
        return $this->link;
    }

    public function priority()
    {
        return $this->priority;
    }

    public function updates()
    {
        return $this->updates;
    }

    public function eqauls($link)
    {
        return $this->link === $link;
    }
}