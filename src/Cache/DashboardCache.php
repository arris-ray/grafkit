<?php

namespace Grafkit\Cache;

use FilesystemIterator;
use Grafkit\Client\DashboardMetadata;
use Grafkit\Client\DashboardMetadataBuilder;
use Grafkit\Lib\Env;
use Grafkit\Lib\Singleton;
use mysql_xdevapi\Warning;

class DashboardCache
{
    use Singleton;

    /**
     * @var string
     */
    public const DASHBOARD_METADATA_FILENAME = 'metadata.json';

    /**
     * @param string $label
     * @param string $uid
     * @param string $json
     * @return int
     */
    public function cacheDashboard(string $label, string $uid, string $json): int
    {
        $this->getOrCreateDashboardCacheDirectory($label);
        $result = file_put_contents($this->getDashboardCacheFilepath($label, $uid), $json);
        return $result === false ? 0 : $result;
    }

    /**
     * @param string $label
     * @param string $json
     * @return int
     */
    public function cacheDashboardMetadatas(string $label, string $json): int
    {
        $this->getOrCreateDashboardCacheDirectory($label);
        $result = file_put_contents($this->getDashboardCacheMetadataFilepath($label), $json);
        return $result === false ? 0 : $result;
    }

    /**
     * @param string $label
     * @param string $uid
     * @return bool
     */
    public function doesDashboardCacheExist(string $label, string $uid): bool
    {
        $cacheDir = implode(DIRECTORY_SEPARATOR, [$this->getDashboardCacheDirectory($label), $label, $uid]);
        if (!file_exists($cacheDir)) {
            return false;
        }

        $fi = new FilesystemIterator($cacheDir, FilesystemIterator::SKIP_DOTS);
        return iterator_count($fi) > 1;
    }

    /**
     * @param string $label
     * @return bool
     */
    public function doesDashboardMetadataCacheExist(string $label): bool
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [
            $this->getDashboardCacheDirectory($label),
            self::DASHBOARD_METADATA_FILENAME
        ]);
        return file_exists($filepath);
    }

    /**
     * @param string $label
     * @param string $uid
     * @return string|null
     */
    public function getCachedDashboard(string $label, string $uid): ?string
    {
        return $this->doesDashboardCacheExist($label, $uid)
            ? file_get_contents($this->getDashboardCacheFilepath($label, $uid))
            : null;
    }

    /**
     * @param string $label
     * @return DashboardMetadata[]|null
     */
    public function getCachedDashboardMetadatas(string $label): ?array
    {
        $dashboardMetadatasJson = $this->getCachedDashboardMetadatasJson($label);
        if ($dashboardMetadatasJson === null) {
            return null;
        }

        // Convert dashboard metadata into structures
        $results = [];
        $dashboardMetadatas = json_decode($dashboardMetadatasJson, true);
        foreach ($dashboardMetadatas as $dashboardMetadata) {
            $results[] = DashboardMetadataBuilder::fromArray($dashboardMetadata);
        }
        return $results;
    }

    /**
     * @param string $label
     * @return string|null
     */
    public function getCachedDashboardMetadatasJson(string $label): ?string
    {
        return $this->doesDashboardMetadataCacheExist($label)
            ? file_get_contents($this->getDashboardCacheMetadataFilepath($label))
            : null;
    }

    /**
     * @param string $label
     * @return string
     */
    private function getDashboardCacheDirectory(string $label): string
    {
        return implode(DIRECTORY_SEPARATOR, [Env::DIR_RESOURCES_CACHE_DASHBOARD, $label]);
    }

    /**
     * @param string $label
     * @param string $uid
     * @return string
     */
    private function getDashboardCacheFilepath(string $label, string $uid): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->getDashboardCacheDirectory($label), $uid]);
    }

    /**
     * @param string $label
     * @return string
     */
    private function getDashboardCacheMetadataFilepath(string $label): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->getDashboardCacheDirectory($label), self::DASHBOARD_METADATA_FILENAME]);
    }

    /**
     * @param string $label
     * @return string
     */
    private function getOrCreateDashboardCacheDirectory(string $label): string
    {
        $dir = $this->getDashboardCacheDirectory($label);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }
}
