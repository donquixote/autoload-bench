<?php

namespace Seld\AutoloadBench\Loader;

/*
 * Copied from Composer sources (MIT licensed)
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * This class is loosely based on the Symfony UniversalClassLoader.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
use Seld\AutoloadBench\LoaderType\AbstractPsr4Loader;

class ComposerSmartPsr4Separate extends AbstractPsr4Loader
{
    // PSR-4
    private $prefixLengthsPsr4 = array();
    private $prefixDirsPsr4 = array();
    private $fallbackDirsPsr4 = array();

    // PSR-0
    private $prefixesPsr0 = array();
    private $fallbackDirsPsr0 = array();

    private $useIncludePath = false;

    /**
     * Registers a set of classes, merging with any others previously set.
     *
     * @param string $prefix
     *   The classes prefix
     * @param array|string $paths
     *   The location(s) of the classes
     * @param bool $prepend
     *   Prepend the location(s)
     */
    public function add($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr0 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr0
                );
            } else {
                $this->fallbackDirsPsr0 = array_merge(
                    $this->fallbackDirsPsr0,
                    (array) $paths
                );
            }

            return;
        }

        $first = $prefix[0];
        if (!isset($this->prefixesPsr0[$first][$prefix])) {
            $this->prefixesPsr0[$first][$prefix] = (array) $paths;

            return;
        }
        if ($prepend) {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                (array) $paths,
                $this->prefixesPsr0[$first][$prefix]
            );
        } else {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                $this->prefixesPsr0[$first][$prefix],
                (array) $paths
            );
        }
    }

    public function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr4 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr4
                );
            } else {
                $this->fallbackDirsPsr4 = array_merge(
                    $this->fallbackDirsPsr4,
                    (array) $paths
                );
            }

            return;
        }

        $prefix = rtrim($prefix, '\\');
        $prefix = $prefix . '\\';

        if (!isset($this->prefixDirsPsr4[$prefix])) {
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = strlen($prefix);
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
            return;
        }
        if ($prepend) {
            $this->prefixDirsPsr4[$prefix] = array_merge(
                (array) $paths,
                $this->prefixDirsPsr4[$prefix]
            );
        } else {
            $this->prefixDirsPsr4[$prefix] = array_merge(
                $this->prefixDirsPsr4[$prefix],
                (array) $paths
            );
        }
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|bool The path if found, false otherwise
     */
    public function findFile($class)
    {
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        $logicalPath = strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            foreach ($this->prefixLengthsPsr4[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($this->prefixDirsPsr4[$prefix] as $dir) {
                        if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPath, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirsPsr4 as $dir) {
            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPath)) {
                return $file;
            }
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $logicalPathPsr0
              = substr($logicalPath, 0, $pos + 1)
              . strtr(substr($logicalPath, $pos + 1), '_', DIRECTORY_SEPARATOR)
            ;
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR);
        }

        if (isset($this->prefixesPsr0[$first])) {
            foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirsPsr0 as $dir) {
            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                return $file;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
            return $file;
        }

        return $this->classMap[$class] = false;
    }
}
