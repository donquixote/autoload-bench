<?php

namespace Seld\AutoloadBench\LoaderType;

use Seld\AutoloadBench\MockFilesystem;

abstract class AbstractLoader
{
    /**
     * @var MockFilesystem
     */
    protected $filesystem;

    public function setFilesystem(MockFilesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string       $class The name of the class
     * @return Boolean|null True, if loaded
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            return true;
        }

        return false;
    }

    public function findFile(
        /** @noinspection PhpUnusedParameterInspection */
        $class)
    {
        return false;
    }
}
