<?php

namespace Seld\AutoloadBench\Loader;

use Seld\AutoloadBench\LoaderType\AbstractPrefixLoader;

class PSR0 extends AbstractPrefixLoader
{
    private $prefixes = array();
    private $fallbackDirs = array();

    /**
     * Registers a set of classes
     *
     * @param string $prefix
     *   The classes prefix
     * @param array|string $paths
     *   The location(s) of the classes
     */
    public function add($prefix, $paths)
    {
        if (!$prefix) {
            foreach ((array) $paths as $path) {
                $this->fallbackDirs[] = $path;
            }

            return;
        }
        if (isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = array_merge(
                $this->prefixes[$prefix],
                (array) $paths
            );
        } else {
            $this->prefixes[$prefix] = (array) $paths;
        }
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|null The path, if found
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function findFile($class)
    {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach ($this->prefixes as $prefix => $dirs) {
            if (0 === strpos($class, $prefix)) {
                foreach ($dirs as $dir) {
                    if ($this->filesystem->file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                        return $dir . DIRECTORY_SEPARATOR . $classPath;
                    }
                }
            }
        }
    }
}
