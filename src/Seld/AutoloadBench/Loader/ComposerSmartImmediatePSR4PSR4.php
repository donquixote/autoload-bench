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

class ComposerSmartImmediatePSR4PSR4 extends AbstractMultiLoader
{
    private $prefixes = array();
    private $fallbackDirs = array();
    private $useIncludePath = false;

    const PSR0 = -1;

    /**
     * Registers a set of classes, merging with any others previously set.
     *
     * @param string       $prefix  The classes prefix
     * @param array|string $paths   The location(s) of the classes
     * @param bool         $prepend Prepend the location(s)
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

        $first = $prefix[0];
        $paths = is_array($paths) ? array_fill_keys($paths, self::PSR0) : array($paths => self::PSR0);
        if (!isset($this->prefixes[$first][$prefix])) {
            $this->prefixes[$first][$prefix] = $paths;
        } elseif ($prepend) {
            $this->prefixes[$first][$prefix] += $paths;
        } else {
            $this->prefixes[$first][$prefix] = $paths + $this->prefixes[$first][$prefix];
        }
    }

    public function addPSR4($prefix, $paths, $prepend = false)
    {
        $prefix = trim($prefix, '\\');
        $prefix = strlen($prefix) ? $prefix . '\\' : '';
        $first = $prefix[0];
        $paths = array_fill_keys((array)$paths, strlen($prefix));
        if (!isset($this->prefixes[$first][$prefix])) {
            $this->prefixes[$first][$prefix] = $paths;
        } elseif ($prepend) {
            $this->prefixes[$first][$prefix] += $paths;
        } else {
            $this->prefixes[$first][$prefix] = $paths + $this->prefixes[$first][$prefix];
        }
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string       $class The name of the class
     * @return Boolean|null True, if loaded
     */
    public function loadClass($class)
    {
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (isset($this->classMap[$class])) {
            $this->classMap[$class];
            return true;
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
            // $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $namespacePath = '';
            // $classPath = null;
            $className = $class;
        }

        $classNamePathPSR0 = strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

        $first = $class[0];
        if (isset($this->prefixes[$first])) {
            foreach ($this->prefixes[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir => $posOrPSR0) {
                        if (self::PSR0 === $posOrPSR0) {
                            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . $namespacePath . $classNamePathPSR0)) {
                                /** @noinspection PhpExpressionResultUnusedInspection */
                                $file;
                                return true;
                            }
                        }
                        else {
                            // PSR-4, and $typeOrPos gives the length of the prefix.
                            if ($this->filesystem->file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($namespacePath, $posOrPSR0) . $className . '.php')) {
                                /** @noinspection PhpExpressionResultUnusedInspection */
                                $file;
                                return true;
                            }
                        }
                    }
                }
            }
        }

        $classPathPSR0 = $namespacePath . $classNamePathPSR0;

        foreach ($this->fallbackDirs as $dir) {
            if ($this->filesystem->file_exists($dir . DIRECTORY_SEPARATOR . $classPathPSR0)) {
                /** @noinspection PhpExpressionResultUnusedInspection */
                $dir . DIRECTORY_SEPARATOR . $classPathPSR0;
                return true;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classPathPSR0)) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            $file;
            return true;
        }

        return $this->classMap[$class] = false;
    }
}
