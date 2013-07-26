<?php

namespace Seld\AutoloadBench\LoaderType;


interface ClassMapLoaderInterface extends ClassLoaderInterface
{
    /**
     * @param array $classMap
     */
    function setClassMap(array $classMap);
}