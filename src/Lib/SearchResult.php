<?php

namespace Grafkit\Lib;

use Grafkit\App;
use Stringable;

readonly class SearchResult implements Stringable
{
    public function __construct(
        public string $label,
        public string $uid,
        public string $url,
        public string $pattern
    ) {}

    /**
     * @return string
     */
    public function __toString(): string
    {
        $hostname = App::getInstance()->getConfiguration()->hostnames->hostnames[$this->label]->url;
        $url = implode('', [$hostname, $this->url]);
        return "{$url}";
    }
}