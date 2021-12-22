<?php

namespace DimaBzz\LaravelConfigWriter\Events;

class WriteSuccess
{
    /**
     * Configuration file name.
     *
     * @var string
     */
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
