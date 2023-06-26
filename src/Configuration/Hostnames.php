<?php

namespace Grafkit\Configuration;

readonly class Hostnames
{
    /**
     * @param Hostname[] $hostnames
     */
    public function __construct(
        public array $hostnames
    ) {}
}