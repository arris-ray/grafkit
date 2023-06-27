<?php

namespace Grafkit\Client;

use Grafkit\Authorization\AuthorizationProvider;
use Grafkit\Authorization\CookieAuthorizationProvider;
use Grafkit\Cache\DashboardCache;
use Grafkit\Exception\GrafanaClientException;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Throwable;

class GrafanaClient
{
    /**
     * @var int
     */
    public const BATCH_SIZE = 100;

    /**
     * @var string
     * @see https://grafana.com/docs/grafana/latest/developers/http_api/folder_dashboard_search/
     */
    public const DASHBOARD_SEARCH_TYPE = 'dash-db';

    /**
     * @var string
     */
    private string $label;

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * @var RequestFactoryInterface
     */
    private RequestFactoryInterface $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private StreamFactoryInterface $streamFactory;

    /**
     * @var UriFactoryInterface
     */
    private UriFactoryInterface $uriFactory;

    /**
     * @var AuthorizationProvider
     */
    private AuthorizationProvider $authorizationProvider;

    /**
     * Constructor
     *
     * @param string $label
     * @param string $baseUrl
     */
    public function __construct(string $label, string $baseUrl)
    {
        $this->label = $label;
        $this->baseUrl = $baseUrl;
//       $this->client = Psr18ClientDiscovery::find();
        $this->client = GuzzleAdapter::createWithConfig(['verify' => false]);
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $this->uriFactory = Psr17FactoryDiscovery::findUriFactory();
        $this->authorizationProvider = new CookieAuthorizationProvider();
    }

    /**
     * @param bool $refreshCache
     * @return DashboardMetadata[]
     * @throws GrafanaClientException
     */
    public function getDashboardMetadatas(bool $refreshCache = false): array
    {
        // Get dashboard metadata from cache
        $dashboardMetadatas = DashboardCache::getInstance()->getCachedDashboardMetadatas($this->label);

        // Refresh the dashboard metadata cache, if necessary
        if ($refreshCache || $dashboardMetadatas === null) {
            $dashboardMetadatas = $this->requestDashboardMetadatas();
            DashboardCache::getInstance()->cacheDashboardMetadatas($this->label, json_encode($dashboardMetadatas));
            $dashboardMetadatas = DashboardCache::getInstance()->getCachedDashboardMetadatas($this->label);
        }
        return $dashboardMetadatas;
    }

    /**
     * @param string $uid
     * @param bool $refreshCache
     * @return string
     * @throws GrafanaClientException
     */
    public function getDashboard(string $uid, bool $refreshCache = false): string
    {
        // Get dashboard from cache
        $dashboard = DashboardCache::getInstance()->getCachedDashboard($this->label, $uid);

        // Refresh the dashboard cache, if necessary
        if ($refreshCache || $dashboard === null) {
            $dashboard = $this->requestDashboard($uid);
            DashboardCache::getInstance()->cacheDashboard($this->label, $uid, $dashboard);
        }
        return $dashboard;
    }

    /**
     * Convenience method for creating a request to submit to the Grafana HTTP API.
     *
     * @param string $method
     * @param string $path
     * @param StreamInterface|null $body
     * @return RequestInterface
     */
    private function createRequest(string $method, string $path, ?StreamInterface $body = null): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest($method, "https://{$this->baseUrl}/{$path}")
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json');

        if ($body !== null) {
            $request = $request->withBody($body);
        }

        return $this->authorizationProvider->authorize($request);
    }

    /**
     * @return string
     * @throws GrafanaClientException
     */
    private function requestDashboardMetadatas(): string
    {
        try {
            $allDashboards = [];
            $page = 1;
            $batchSize = self::BATCH_SIZE;
            $type = self::DASHBOARD_SEARCH_TYPE;
            $query = '';

            // Request metadata for all dashboards
            do {
                $path = "api/search?type={$type}&limit={$batchSize}&query={$query}&page={$page}";
                $request = $this->createRequest("GET", $path);
                $response = $this->client->sendRequest($request);
                $json = $response->getBody()->getContents();
                $responseDashboards = json_decode($json, true);
                $allDashboards = array_merge($allDashboards, $responseDashboards);
            } while ($page++ && !empty($responseDashboards));

            // Return dashboard metadata
            return json_encode($allDashboards);
        } catch (Throwable $e) {
            throw new GrafanaClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $uid
     * @return string
     * @throws GrafanaClientException
     */
    private function requestDashboard(string $uid): string
    {
        try {
            // Request dashboard
            $request = $this->createRequest("GET", "api/dashboards/uid/{$uid}");
            $response = $this->client->sendRequest($request);
            return $response->getBody()->getContents();
        } catch (Throwable $e) {
            throw new GrafanaClientException($e->getMessage(), $e->getCode(), $e);
        }
    }
}