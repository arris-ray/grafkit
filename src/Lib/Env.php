<?php

namespace Grafkit\Lib;

class Env
{
    public const DIR_ROOT = '/app';
    public const DIR_CONFIG = self::DIR_ROOT . DIRECTORY_SEPARATOR . 'config';
    public const DIR_RESOURCES = self::DIR_ROOT . DIRECTORY_SEPARATOR . 'resources';
    public const DIR_RESOURCES_CACHE = self::DIR_RESOURCES . DIRECTORY_SEPARATOR . 'cache';
    public const DIR_RESOURCES_CACHE_DASHBOARD = self::DIR_RESOURCES_CACHE . DIRECTORY_SEPARATOR . 'dashboard';
    public const RESOURCE_COOKIES = self::DIR_RESOURCES . DIRECTORY_SEPARATOR . 'cookies';
}