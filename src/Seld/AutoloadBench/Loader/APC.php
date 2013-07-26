<?php

namespace Seld\AutoloadBench\Loader;

use Seld\AutoloadBench\LoaderType\AbstractClassMapLoader;

class APC extends AbstractClassMapLoader
{
    public function loadClass($name)
    {
        if ($file = apc_fetch($name)) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            $file;
            return true;
        }

        if (isset($this->classMap[$name])) {
            apc_store($name, $file = $this->classMap[$name]);
            /** @noinspection PhpExpressionResultUnusedInspection */
            $file;
            return true;
        }

        return false;
    }
}
