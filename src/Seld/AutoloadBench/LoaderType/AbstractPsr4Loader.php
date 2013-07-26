<?php

namespace Seld\AutoloadBench\LoaderType;

use Seld\AutoloadBench\LoaderType\AbstractClassMapLoader;
use Seld\AutoloadBench\LoaderType\PrefixLoaderInterface;

abstract class AbstractPsr4Loader extends AbstractMultiLoader implements Psr4LoaderInterface
{
    public function setPrefixesPsr4(array $prefixes)
    {
        foreach ($prefixes as $prefix => $baseDir) {
            $this->addPsr4($prefix, $baseDir);
        }
    }

    public function addPsr4($prefix, $baseDir)
    {
        // Empty by default.
    }
}
