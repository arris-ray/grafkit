<?php

namespace Grafkit\Configuration;

use Grafkit\Exception\ConfigurationException;
use Grafkit\Lib\Singleton;

class Loader
{
    use Singleton;

    /**
     * @param string|null $filePath
     * @return Configuration
     * @throws ConfigurationException
     */
    public function load(?string $filePath = null): Configuration
    {
        // Read file
        $filePath = $filePath ?? Configuration::DEFAULT_FILEPATH;
        $yaml = yaml_parse_file($filePath);

        // Validate root key
        $rootKey = Configuration::KEY_ROOT;
        $configuration = $yaml[$rootKey] ?? null;
        if ($configuration === null) {
            throw new ConfigurationException("Configuration file is missing expected root key named {$rootKey}.");
        }

        // Parse sections
        $builder = Configuration::newBuilder();
        foreach ($configuration as $section => $settings) {
            switch ($section) {
                case Configuration::KEY_HOSTNAMES:
                    $hostnames = $this->parseSectionHostnames($settings);
                    $builder->withHostnames($hostnames);
                    break;
                default:
                    throw new ConfigurationException("Configuration file contains unrecognized section named {$section}.");
            }
        }

        // Build configuration
        return $builder->build();
    }

    /**
     * @param array $settings
     * @return Hostnames
     */
    private function parseSectionHostnames(array $settings): Hostnames
    {
        $hostnames = [];
        foreach ($settings as $label => $url) {
            $hostnames[] = new Hostname($label, $url);
        }
        return new Hostnames($hostnames);
    }
}
