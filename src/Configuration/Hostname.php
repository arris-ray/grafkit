<?php

namespace Grafkit\Configuration;

readonly class Hostname
{
    /**
     * @param string $label
     * @param string $url
     */
    public function __construct(
        public string $label,
        public string $url
    ) {}
}
