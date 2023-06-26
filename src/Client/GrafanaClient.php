<?php

namespace Grafkit\Client;

use Grafkit\Authorization\AuthorizationProvider;
use Grafkit\Authorization\CookieAuthorizationProvider;
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
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
//       $this->client = Psr18ClientDiscovery::find();
        $this->client = GuzzleAdapter::createWithConfig(['verify' => false]);
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $this->uriFactory = Psr17FactoryDiscovery::findUriFactory();
        $this->authorizationProvider = new CookieAuthorizationProvider();
    }

    /**
     * @param string $uid
     * @return string
     * @throws GrafanaClientException
     */
    public function getDashboard(string $uid): string
    {
        try {
            $request = $this->createRequest("GET", "api/dashboards/uid/{$uid}");
            $response = $this->client->sendRequest($request);
            return $response->getBody()->getContents();
        } catch (Throwable $e) {
            throw new GrafanaClientException($e->getMessage(), $e->getCode(), $e);
        }
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
}