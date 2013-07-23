<?php

namespace Seld\AutoloadBench\Loader;


interface ClassMapLoaderInterface extends ClassLoaderInterface
{
    /**
     * @param array $classMap
     */
    function setClassMap(array $classMap);
}