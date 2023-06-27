<?php

namespace Grafkit\Lib;

readonly class GrepResult
{
    public function __construct(
        public string $label,
        public string $uid,
        public string $url,
        public string $pattern
    ) {}
}