<?php

namespace Grafkit\Configuration;

class ConfigurationBuilder
{
    private Hostnames $hostnames;

    /**
     * @param Hostnames $hostnames
     * @return ConfigurationBuilder
     */
    public function withHostnames(Hostnames $hostnames): ConfigurationBuilder
    {
        $this->hostnames = $hostnames;
        return $this;
    }

    /**
     * @return Configuration
     */
    public function build(): Configuration
    {
        return new Configuration(
            $this->hostnames
        );
    }
}