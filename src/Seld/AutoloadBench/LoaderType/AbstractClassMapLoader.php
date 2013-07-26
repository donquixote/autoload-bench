<?php

namespace Seld\AutoloadBench\LoaderType;

use Seld\AutoloadBench\LoaderType\AbstractLoader;
use Seld\AutoloadBench\LoaderType\ClassMapLoaderInterface;

abstract class AbstractClassMapLoader extends AbstractLoader implements ClassMapLoaderInterface
{
    protected $classMap = [];

    function setClassMap(array $classMap)
    {
        $this->classMap = $classMap;
    }
}
