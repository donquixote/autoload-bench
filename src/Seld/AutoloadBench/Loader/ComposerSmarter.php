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
use Seld\AutoloadBench\LoaderType\AbstractMultiLoader;

class ComposerSmarter extends AbstractMultiLoader
{
    private $prefixes = array();
    private $fallbackDirs = array();
    private $useIncludePath = false;
    private $magicIndex = 15;

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
                $this->fallbackDirs = array_merge(
                    (array) $paths,
                    $this->fallbackDirs
                );
            } else {
                $this->fallbackDirs = array_merge(
                    $this->fallbackDirs,
                    (array) $paths
                );
            }

            return;
        }

        $indicator = $prefix[0] . (isset($prefix[$this->magicIndex]) ? $prefix[$this->magicIndex] : '');
        if (!isset($this->prefixes[$indicator][$prefix])) {
            $this->prefixes[$indicator][$prefix] = (array) $paths;

            return;
        }
        if ($prepend) {
            $this->prefixes[$indicator][$prefix] = array_merge(
                (array) $paths,
                $this->prefixes[$indicator][$prefix]
            );
        } else {
            $this->prefixes[$indicator][$prefix] = array_merge(
                $this->prefixes[$indicator][$prefix],
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

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

        $first = $class[0];
        if (isset($class[$this->magicIndex])) {
            $indicator = $first . $class[$this->magicIndex];
            if (isset($this->prefixes[$indicator])) {
                foreach ($this->prefixes[$indicator] as $prefix => $dirs) {
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
        if (isset($this->prefixes[$first])) {
            foreach ($this->prefixes[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if ($this->filesystem->file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                            return $dir . DIRECTORY_SEPARATOR . $classPath;
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirs as $dir) {
            if ($this->filesystem->file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                return $dir . DIRECTORY_SEPARATOR . $classPath;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
            return $file;
        }

        return $this->classMap[$class] = false;
    }
}
