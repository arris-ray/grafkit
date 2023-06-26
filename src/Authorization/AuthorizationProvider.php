<?php

namespace Grafkit\Authorization;

use Grafkit\Exception\AuthorizationException;
use Psr\Http\Message\RequestInterface;

interface AuthorizationProvider
{
    /**
     * @param RequestInterface $request
     * @return RequestInterface
     * @throws AuthorizationException
     */
    public function authorize(RequestInterface $request): RequestInterface;
}