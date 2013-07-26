<?php

namespace Seld\AutoloadBench\LoaderType;

use Seld\AutoloadBench\LoaderType\AbstractLoader;
use Seld\AutoloadBench\LoaderType\PrefixLoaderInterface;

abstract class AbstractPrefixLoader extends AbstractLoader implements PrefixLoaderInterface
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
