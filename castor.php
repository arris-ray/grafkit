<?php

namespace grafkit;

use Castor\Attribute\AsTask;
use Grafkit\App;
use Grafkit\Lib\SearchResult;

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
    displaySearchResults($searchResults);
}

#[AsTask]
function replace(string $search, string $replace): void
{
    $searchResults = App::getInstance()->searchDashboards("$search");
    displaySearchResults($searchResults);
    promptForConfirmation();

    $replaceResults = App::getInstance()->replaceInDashboards($search, $replace, $searchResults);
    displayReplaceResults($replaceResults);
}

/**
 * @param SearchResult[] $searchResults
 * @return void
 */
function displaySearchResults(array $searchResults): void
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

    // Display dashboard URLs
    echo PHP_EOL;
    echo "# DASHBOARDS" . PHP_EOL;
    foreach ($displayUrls as $displayUrl) {
        echo ("- {$displayUrl}" . PHP_EOL);
    }
    echo PHP_EOL;

    // Display summary
    $numResults = count($searchResults);
    $numUrls = count($uniqueUrls);
    $numLabels = count($uniqueLabels);
    echo "# SUMMARY" . PHP_EOL;
    echo "- {$numResults} string matches" . PHP_EOL;
    echo "- {$numUrls} dashboards" . PHP_EOL;
    echo "- {$numLabels} Grafana instances" . PHP_EOL;
    echo PHP_EOL;
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
 * @param array $replaceResults
 * @return void
 */
function displayReplaceResults(array $replaceResults): void
{
    echo PHP_EOL;
    echo "# RESULTS" . PHP_EOL;
    foreach ($replaceResults as $replaceResult) {
        echo ("- {$replaceResult}" . PHP_EOL);
    }
    echo PHP_EOL;
}
