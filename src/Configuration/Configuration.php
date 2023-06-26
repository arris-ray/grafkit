<?php

namespace Grafkit\Configuration;

use Grafkit\Lib\Env;

class Configuration
{
    /**
     * @var string
     */
    public const KEY_ROOT = 'grafkit';

    /**
     * @var string
     */
    public const KEY_HOSTNAMES = 'hostnames';

    /**
     * @var string
     */
    public const DEFAULT_FILENAME = 'grafkit.yaml';

    /**
     * @var string
     */
    public const DEFAULT_FILEPATH = Env::DIR_CONFIG . DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME;

    /**
     * @param Hostnames $hostnames
     */
    public function __construct(
        public Hostnames $hostnames
    ) {}

    /**
     * @return ConfigurationBuilder
     */
    public static function newBuilder(): ConfigurationBuilder
    {
        return new ConfigurationBuilder();
    }
}