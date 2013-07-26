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
class ComposerSmartPsr4Combined extends AbstractMultiLoader
{
    private $prefixLengths = array();
    private $prefixDirs = array();
    private $fallbackDirs = array();
    private $useIncludePath = false;

    const PSR0 = 1;
    const PSR4 = 2;

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
        $paths = is_array($paths) ? array_fill_keys($paths, self::PSR0) : array($paths => self::PSR0);
        $this->addPrefixPaths($prefix, $paths, $prepend);
    }

    public function addPsr4($prefix, $paths, $prepend = false)
    {
        $paths = is_array($paths) ? array_fill_keys($paths, self::PSR4) : array($paths => self::PSR4);
        $this->addPrefixPaths($prefix, $paths, $prepend);
    }

    protected function addPrefixPaths($prefix, array $paths, $prepend)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirs = array_merge(
                    (array) $paths,
                    $this->fallbackDirs
                );
            }
            else {
                $this->fallbackDirs = array_merge(
                    $this->fallbackDirs,
                    (array) $paths
                );
            }

            return;
        }

        if (!isset($this->prefixDirs[$prefix])) {
            // This is a new prefix.
            $this->prefixLengths[$prefix[0]][$prefix] = strlen($prefix);
            $this->prefixDirs[$prefix] = $paths;
        }
        elseif ($prepend) {
            $this->prefixDirs[$prefix] += $paths;
        }
        else {
            $this->prefixDirs[$prefix] = $paths + $this->prefixDirs[$prefix];
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
        if (isset($this->prefixLengths[$first])) {
            foreach ($this->prefixLengths[$first] as $prefix => $length) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($this->prefixDirs[$prefix] as $dir => $type) {
                        if (self::PSR4 === $type) {
                            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPath, $length))) {
                                return $file;
                            }
                        }
                        else {
                            if (!isset($logicalPathPsr0)) {
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
                            }
                            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                                return $file;
                            }
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirs as $dir => $tpye) {
            if (self::PSR4 === $type) {
                if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPath)) {
                    return $file;
                }
            }
            else {
                if (!isset($logicalPathPsr0)) {
                    if (false !== $pos = strrpos($class, '\\')) {
                        // namespaced class name
                        $logicalPathPsr0
                          = substr($logicalPath, 0, $pos + 1)
                          . strtr(substr($class, $pos + 1), '_', DIRECTORY_SEPARATOR)
                        ;
                    } else {
                        // PEAR-like class name
                        $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR);
                    }
                }
                if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                    return $file;
                }
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
            return $file;
        }

        return $this->classMap[$class] = false;
    }
}
