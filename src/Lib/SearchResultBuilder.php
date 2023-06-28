<?php

namespace Grafkit\Lib;

use Grafkit\Cache\DashboardCache;
use Grafkit\Client\DashboardMetadata;

class SearchResultBuilder
{
    private string $path;
    private string $pattern;

    /**
     * @param string $result
     * @return SearchResult|null
     */
    public static function fromString(string $result): ?SearchResult
    {
        if (empty($result)) {
            return null;
        }

        $tokens = explode(':', $result, 2);
        $path = $tokens[0];
        $pattern = $tokens[1];
        return SearchResultBuilder::new()
            ->withPath($path)
            ->withPattern($pattern)
            ->build();
    }

    /**
     * @return SearchResultBuilder
     */
    public static function new(): SearchResultBuilder
    {
        return new SearchResultBuilder();
    }

    /**
     * @param string $path
     * @return SearchResultBuilder
     */
    public function withPath(string $path): SearchResultBuilder
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $pattern
     * @return SearchResultBuilder
     */
    public function withPattern(string $pattern): SearchResultBuilder
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return SearchResult
     */
    public function build(): SearchResult
    {
        $label = '';
        $dashboardMetadata = $this->getDashboardMetadata($this->path, $label);
        return new SearchResult(
            $label,
            $dashboardMetadata->uid,
            $dashboardMetadata->url,
            $this->pattern
        );
    }

    /**
     * @param string $cachePath
     * @param string $label
     * @return DashboardMetadata|null
     */
    private function getDashboardMetadata(string $cachePath, string & $label): ?DashboardMetadata
    {
        // Split the result into the path and pattern-match components
        $tokens = explode(DIRECTORY_SEPARATOR, $cachePath);
        $reverseTokens = array_reverse($tokens);
        $filename = array_shift($reverseTokens);
        $label = array_shift($reverseTokens);

        // Lookup the dashboard URL by its UID
        $dashboardMetadatas = DashboardCache::getInstance()->getCachedDashboardMetadatas($label);
        foreach ($dashboardMetadatas as $dashboardMetadata) {
            if ($dashboardMetadata->uid === $filename) {
                return $dashboardMetadata;
            }
        }
        return null;
    }
}
