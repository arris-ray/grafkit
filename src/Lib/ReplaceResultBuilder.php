<?php

namespace Grafkit\Lib;

class ReplaceResultBuilder
{
    /**
     * @var bool
     */
    private bool $wasReplaced;

    /**
     * @var SearchResult
     */
    private SearchResult $searchResult;

    /**
     * @return ReplaceResultBuilder
     */
    public static function new(): ReplaceResultBuilder
    {
        return new ReplaceResultBuilder();
    }

    /**
     * @param SearchResult $searchResult
     * @return ReplaceResultBuilder
     */
    public function withSearchResult(SearchResult $searchResult): ReplaceResultBuilder
    {
        $this->searchResult = $searchResult;
        return $this;
    }

    /**
     * @param bool $wasReplaced
     * @return ReplaceResultBuilder
     */
    public function withWasReplaced(bool $wasReplaced): ReplaceResultBuilder
    {
        $this->wasReplaced = $wasReplaced;
        return $this;
    }

    /**
     * @return ReplaceResult
     */
    public function build(): ReplaceResult
    {
        return new ReplaceResult(
            $this->searchResult->label,
            $this->searchResult->uid,
            $this->searchResult->url,
            $this->searchResult->pattern,
            $this->wasReplaced
        );
    }
}