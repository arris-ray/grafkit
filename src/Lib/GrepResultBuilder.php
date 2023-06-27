<?php

namespace Grafkit\Lib;

use Grafkit\Cache\DashboardCache;

class GrepResultBuilder
{
    private string $path;
    private string $pattern;

    /**
     * @param string $result
     * @return GrepResult|null
     */
    public static function fromString(string $result): ?GrepResult
    {
        if (empty($result)) {
            return null;
        }

        $tokens = explode(':', $result, 2);
        $path = $tokens[0];
        $pattern = $tokens[1];
        return GrepResultBuilder::new()
            ->withPath($path)
            ->withPattern($pattern)
            ->build();
    }

    /**
     * @return GrepResultBuilder
     */
    public static function new(): GrepResultBuilder
    {
        return new GrepResultBuilder();
    }

    /**
     * @param string $path
     * @return GrepResultBuilder
     */
    public function withPath(string $path): GrepResultBuilder
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $pattern
     * @return GrepResultBuilder
     */
    public function withPattern(string $pattern): GrepResultBuilder
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return GrepResult
     */
    public function build(): GrepResult
    {
        return new GrepResult(
            $this->lookupDashboardUrl($this->path),
            $this->pattern
        );
    }

    /**
     * @param string $cachePath
     * @return string|null
     */
    private function lookupDashboardUrl(string $cachePath): ?string
    {
        // Split the result into the path and pattern-match components
        $tokens = explode(DIRECTORY_SEPARATOR, $cachePath);
        $reverseTokens = array_reverse($tokens);
        $filename = array_shift($reverseTokens);
        $label = array_shift($reverseTokens);

        // Lookup the dashboard URL by its UID
        $jsonDashboardMetadatas = DashboardCache::getInstance()->getCachedDashboardMetadatas($label);
        foreach ($jsonDashboardMetadatas as $jsonDashboardMetadata) {
            if ($jsonDashboardMetadata->uid === $filename) {
                return $jsonDashboardMetadata->url;
            }
        }
        return null;
    }
}