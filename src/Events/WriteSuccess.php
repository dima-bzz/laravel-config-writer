<?php

namespace DimaBzz\LaravelConfigWriter\Events;

class WriteSuccess
{
    /**
     * Configuration file name.
     *
     * @var string
     */
    public string $configFile;

    public function __construct($configFile)
    {
        $this->configFile = $configFile;
    }
}
