<?php

namespace Grafkit;

use Grafkit\Cache\DashboardCache;
use Grafkit\Client\DashboardMetadata;
use Grafkit\Client\GrafanaClient;
use Grafkit\Configuration\Configuration;
use Grafkit\Configuration\Loader;
use Grafkit\Lib\Env;
use Grafkit\Lib\GrepResult;
use Grafkit\Lib\GrepResultBuilder;
use Grafkit\Lib\Singleton;

class App
{
    use Singleton;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configuration = Loader::getInstance()->load();
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
     * Returns a list of all cached dashboards that contain the given {@see $regex} pattern.
     *
     * @param string $regex
     * @return GrepResult[]
     */
    public function searchDashboards(string $regex): array
    {
        $dir = Env::DIR_RESOURCES_CACHE_DASHBOARD;
        $metadataFilename = DashboardCache::DASHBOARD_METADATA_FILENAME;
        $command = "grep -oR \"{$regex}\" {$dir} --exclude '{$metadataFilename}' | uniq";
        $output = shell_exec($command);
        $output ??= "";

        $results = [];
        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            $grepResult = GrepResultBuilder::fromString($line);
            if ($grepResult !== null) {
                $results[] = $grepResult;
            }
        }
        return $results;
    }
}