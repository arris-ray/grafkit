<?php

namespace Grafkit;

use Grafkit\Cache\DashboardCache;
use Grafkit\Client\GrafanaClient;
use Grafkit\Configuration\Configuration;
use Grafkit\Configuration\Loader;
use Grafkit\Exception\ConfigurationException;
use Grafkit\Exception\GrafanaClientException;
use Grafkit\Lib\Env;
use Grafkit\Lib\ReplaceResult;
use Grafkit\Lib\ReplaceResultBuilder;
use Grafkit\Lib\SearchResult;
use Grafkit\Lib\SearchResultBuilder;
use Grafkit\Lib\Singleton;

class App
{
    use Singleton;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var GrafanaClient[]
     */
    private array $clients;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configuration = Loader::getInstance()->load();
        $this->clients = [];
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param string|null $label
     * @return void
     * @throws Exception\GrafanaClientException
     */
    public function refreshDashboardCache(?string $label = null): void
    {
        $hostnames = $this->configuration->hostnames->hostnames;
        if ($label !== null && array_key_exists($label, $hostnames)) {
            $hostnames = [$hostnames[$label]];
        }

        foreach ($hostnames as $hostname) {
            $client = new GrafanaClient($hostname->label, $hostname->url);
            $dashboards = $client->getDashboardMetadatas(true);
            foreach ($dashboards as $dashboard) {
                $client->getDashboard($dashboard->uid, true);
            }
        }
    }

    /**
     * Returns a list of all cached dashboards that contain the given {@see $search} pattern.
     *
     * @internal Performs exact string matches.
     * @param string $search
     * @return SearchResult[]
     */
    public function searchDashboards(string $search): array
    {
        $dir = Env::DIR_RESOURCES_CACHE_DASHBOARD;
        $metadataFilename = DashboardCache::DASHBOARD_METADATA_FILENAME;
        $command = "grep -oRw \"{$search}\" {$dir} --exclude '{$metadataFilename}'";
        $output = shell_exec($command);
        $output ??= "";

        $results = [];
        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            $grepResult = SearchResultBuilder::fromString($line);
            if ($grepResult !== null) {
                $results[] = $grepResult;
            }
        }
        return $results;
    }

    /**
     * @param string $search
     * @param string $replace
     * @param SearchResult[]|null $searchResults
     * @return ReplaceResult[]
     * @throws ConfigurationException|GrafanaClientException
     */
    public function replaceInDashboards(string $search, string $replace, ?array $searchResults = null): array
    {
        // Perform a search for affected dashboards if we weren't give the result set
        $searchResults = $searchResults ?? App::getInstance()->searchDashboards("$search");

        // Perform string replacements and commit updated dashboards to upstream Grafana instances
        $replaceResults = [];
        foreach ($searchResults as $searchResult) {
            // Get the current dashboard for the current result
            $dashboardJson = $this->getGrafanaClient($searchResult->label)->getDashboard($searchResult->uid, true);

            // Process string replacements in the dashboard
            $updatedDashboardJson = str_replace($search, $replace, $dashboardJson);

            // Commit the updated dashboard
            $wasUpdated = $this->getGrafanaClient($searchResult->label)->updateDashboard(
                $updatedDashboardJson,
                "Grafkit replaced '{$search}' with '{$replace}'"
            );

            // Update our cached copy if we successfully updated the remote Grafana instance
            if ($wasUpdated) {
                DashboardCache::getInstance()->cacheDashboard($searchResult->label, $searchResult->uid, $updatedDashboardJson);
            }

            $replaceResults[] = ReplaceResultBuilder::new()
                ->withSearchResult($searchResult)
                ->withWasReplaced($wasUpdated)
                ->build();
        }
        return $replaceResults;
    }

    /**
     * @param string $label
     * @return GrafanaClient
     * @throws ConfigurationException
     */
    private function getGrafanaClient(string $label): GrafanaClient
    {
        $hostname = $this->getConfiguration()->hostnames->hostnames[$label] ?? null;
        if ($hostname === null) {
            throw new ConfigurationException("Failed to find Grafana hostname with label named {$label}.");
        }

        $this->clients[$label] = $this->clients[$label] ?? new GrafanaClient($label, $hostname->url);
        return $this->clients[$label];
    }
}
