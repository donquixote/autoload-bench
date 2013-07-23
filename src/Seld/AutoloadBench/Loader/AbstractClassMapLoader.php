<?php

namespace Seld\AutoloadBench\Loader;

abstract class AbstractClassMapLoader extends AbstractLoader implements ClassMapLoaderInterface
{
    protected $classMap = [];

    function setClassMap(array $classMap)
    {
        $this->classMap = $classMap;
    }
}
