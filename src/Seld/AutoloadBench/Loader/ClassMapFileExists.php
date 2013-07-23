<?php

namespace Seld\AutoloadBench\Loader;

class ClassMapFileExists extends AbstractClassMapLoader
{
    public function loadClass($name)
    {
        if (isset($this->classMap[$name])) {
            if ($this->filesystem->file_exists($file = $this->classMap[$name])) {
                /** @noinspection PhpExpressionResultUnusedInspection */
                $file;
                return true;
            }
        }

        return false;
    }
}
