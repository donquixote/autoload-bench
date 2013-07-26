<?php

namespace Seld\AutoloadBench\LoaderType;

use Seld\AutoloadBench\LoaderType\AbstractClassMapLoader;
use Seld\AutoloadBench\LoaderType\PrefixLoaderInterface;

abstract class AbstractMultiLoader extends AbstractClassMapLoader implements MultiLoaderInterface
{
    public function setPrefixes(array $prefixes)
    {
        foreach ($prefixes as $prefix => $baseDir) {
            $this->add($prefix, $baseDir);
        }
    }

    public function add($prefix, $baseDir)
    {
        // Empty by default.
    }
}
