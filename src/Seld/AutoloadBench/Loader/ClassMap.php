<?php

namespace Seld\AutoloadBench\Loader;

use Seld\AutoloadBench\LoaderType\AbstractClassMapLoader;

class ClassMap extends AbstractClassMapLoader
{
    public function loadClass($name)
    {
        if (isset($this->classMap[$name])) {
            $this->classMap[$name];

            return true;
        }

        return false;
    }
}
