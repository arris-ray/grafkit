<?php

namespace Grafkit\Lib;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class ReplacementPairs implements IteratorAggregate
{
    /**
     * @var string
     */
    public const PAIR_DELIMITER = ',';

    /**
     * @var string[]
     */
    private array $pairs;

    /**
     * @param string $filename
     * @return ReplacementPairs
     */
    public static function fromFile(string $filename): ReplacementPairs
    {
        $pairs = [];
        $contents = file_get_contents($filename);
        $lines = explode(PHP_EOL, $contents);
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $tokens = explode(self::PAIR_DELIMITER, $line, 2);
            $search = $tokens[0];
            $replace = $tokens[1];
            $pairs[$search] = $replace;
        }

        return new ReplacementPairs($pairs);
    }

    /**
     * Constructor
     *
     * @param string[] $pairs
     */
    public function __construct(array $pairs)
    {
        $this->pairs = $pairs;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->pairs);
    }
}