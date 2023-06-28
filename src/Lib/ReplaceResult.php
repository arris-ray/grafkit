<?php

namespace Grafkit\Lib;

use Grafkit\App;
use Stringable;

readonly class ReplaceResult implements Stringable
{
    /**
     * Constructor.
     *
     * @param string $label
     * @param string $uid
     * @param string $url
     * @param string $pattern
     * @param bool $wasReplaced
     */
    public function __construct(
        public string $label,
        public string $uid,
        public string $url,
        public string $pattern,
        public bool $wasReplaced
    ) {}

    /**
     * @return string
     */
    public function __toString(): string
    {
        $hostname = App::getInstance()->getConfiguration()->hostnames->hostnames[$this->label]->url;
        $url = implode('', [$hostname, $this->url]);
        $status = $this->wasReplaced ? "✅" : "❌";
        return "{$status} $url";
    }
}
