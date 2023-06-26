<?php

namespace Grafkit\Lib;

class Env
{
    public const DIR_ROOT = __DIR__ . '/../../';
    public const DIR_CONFIG = self::DIR_ROOT . DIRECTORY_SEPARATOR . 'config';
    public const DIR_RESOURCES = self::DIR_ROOT . DIRECTORY_SEPARATOR . 'resources';
    public const RESOURCE_COOKIES = self::DIR_RESOURCES . DIRECTORY_SEPARATOR . 'cookies';
}