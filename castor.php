<?php

namespace grafkit;

use Castor\Attribute\AsTask;
use Grafkit\App;
use Grafkit\Lib\ReplacementPairs;
use Grafkit\Lib\SearchResult;

#[AsTask]
function batch_replace(string $filename, bool $dry_run = false): void
{
    $pairs = ReplacementPairs::fromFile($filename);
    foreach ($pairs as $search => $replace) {
        replace($search, $replace, $dry_run);
    }
}

#[AsTask]
function cache(string $label): void
{
    $label = ($label !== "") ? $label : null;
    App::getInstance()->refreshDashboardCache($label);
}

#[AsTask]
function find(string $search): void
{
    $searchResults = App::getInstance()->searchDashboards("$search");
    displaySearchResults($searchResults, $search);
}

#[AsTask]
function replace(string $search, string $replace, bool $dry_run): void
{
    $searchResults = App::getInstance()->searchDashboards("$search");
    displaySearchResults($searchResults, $search, $replace);

    if ($dry_run === false) {
        promptForConfirmation();
        $replaceResults = App::getInstance()->replaceInDashboards($search, $replace, $searchResults);
        displayReplaceResults($replaceResults);
    }
}

/**
 * @return void
 */
function promptForConfirmation(): void
{
    echo "Are you sure you want to proceed? (y/N) ";
    $confirmation = trim(fgets(STDIN));
    if ($confirmation !== 'y') {
        echo "Bye!" . PHP_EOL . PHP_EOL;
        exit (0);
    }
}

/**
 * @param SearchResult[] $searchResults
 * @param string $search
 * @param string|null $replace
 * @return void
 */
function displaySearchResults(array $searchResults, string $search, ?string $replace = null): void
{
    // Get unique counts
    $uniqueUrls = [];
    $uniqueLabels = [];
    foreach ($searchResults as $searchResult) {
        $domain = App::getInstance()->getConfiguration()->hostnames->hostnames[$searchResult->label]->url;
        $url = implode('', [$domain, $searchResult->url]);
        $uniqueUrls[$url] = true;
        $uniqueLabels[$searchResult->label] = true;
    }

    // Prepare display URLs
    $displayUrls = array_keys($uniqueUrls);
    sort($displayUrls);

    // Display search parameters
    echo PHP_EOL;
    echo "---" . PHP_EOL;
    echo "# SEARCH PARAMETERS" . PHP_EOL;
    echo "- Search: {$search}" . PHP_EOL;
    if ($replace !== null) {
        echo "- Replace: {$replace}" . PHP_EOL;
    }
    echo PHP_EOL;

    // Display dashboard URLs
    echo "# DASHBOARDS" . PHP_EOL;
    foreach ($displayUrls as $displayUrl) {
        echo ("- {$displayUrl}" . PHP_EOL);
    }
    echo PHP_EOL;

    // Display summary
    $numResults = count($searchResults);
    $numUrls = count($uniqueUrls);
    $numLabels = count($uniqueLabels);
    echo "# SEARCH RESULTS" . PHP_EOL;
    echo "- {$numResults} string matches" . PHP_EOL;
    echo "- {$numUrls} dashboards" . PHP_EOL;
    echo "- {$numLabels} Grafana instances" . PHP_EOL;
    echo PHP_EOL;
}

/**
 * @param array $replaceResults
 * @return void
 */
function displayReplaceResults(array $replaceResults): void
{
    echo PHP_EOL;
    echo "# REPLACE RESULTS" . PHP_EOL;
    foreach ($replaceResults as $replaceResult) {
        echo ("- {$replaceResult}" . PHP_EOL);
    }
    echo '---' . PHP_EOL;
}
