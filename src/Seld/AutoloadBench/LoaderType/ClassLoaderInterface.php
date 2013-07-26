<?php

namespace Seld\AutoloadBench\LoaderType;


use Seld\AutoloadBench\MockFilesystem;

interface ClassLoaderInterface
{
    /**
     * @param string $class
     */
    function loadClass($class);

    /**
     * @param MockFilesystem $filesystem
     */
    function setFilesystem(MockFilesystem $filesystem);
}